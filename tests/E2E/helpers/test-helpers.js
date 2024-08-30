const path = require('path');
const { test, expect } = require('@playwright/test');

const url = process.env.URL;

const defaultGVAdminURL = `${url}/wp-admin/edit.php?post_type=gravityview`;

const storageState = path.join(__dirname, '../setup/.state.json');

/**
 * Selects a Gravity Form from the dropdown by matching the form title.
 * Throws an error if the form title is not found.
 *
 * @param {object} page - The Playwright page object.
 * @param {string} formTitle - The title of the Gravity Form to select.
 * @throws Will throw an error if the form title is not found.
 */
async function selectGravityFormByTitle(page, formTitle) {
  const formSelector = '#gravityview_form_id';

  await page.waitForSelector(formSelector);

  const optionValue = await page.evaluate(
    ({ formTitle, selector }) => {
      const select = document.querySelector(selector);
      const options = Array.from(select.options);
      const lowerCaseFormTitle = formTitle.toLowerCase();
      const option = options.find((opt) =>
        opt.textContent.trim().toLowerCase().startsWith(lowerCaseFormTitle)
      );
      return option ? option.value : '';
    },
    { formTitle, selector: formSelector }
  );

  if (optionValue) {
    await page.selectOption(formSelector, optionValue);
  } else {
    throw new Error(`Form with title "${formTitle}" not found.`);
  }
}

/**
 * Navigates to the specified URL (or default URL) and ensures the user is logged in.
 * 
 * TODO: Update the wpLogin helper to verify that the old state file is valid 
 * before attempting to use it.
 * 
 * @param {import('playwright').Page} page - The Playwright page object.
 * @param {import('@playwright/test').TestInfo} testInfo - Playwright's test information object.
 * @param {string} [url=defaultGVAdminURL] - The URL to navigate to and check login status.
 * @param {string} [stateFile=storageState] - The path to the storage state file.
 */
async function gotoAndEnsureLoggedIn(page, testInfo = null, url = defaultGVAdminURL, stateFile = storageState) {
  await page.goto(url);

  const adminBarSelector = '#wpadminbar';
  const isLoggedIn = await page.$(adminBarSelector);
  const skipMessage = 'User not logged in. Delete old state file and try again.';


  if (!isLoggedIn) {
    console.log(skipMessage);
    testInfo ? testInfo.skip(!isLoggedIn, skipMessage) : test.skip(skipMessage);
  }
}

/**
 * Helper function to create a GravityView.
 *
 * @param {import('playwright').Page} page - The Playwright page object.
 * @param {Object} params - Parameters for creating the view.
 * @param {string} params.formTitle - The title of the Gravity Form to select.
 * @param {string} params.viewName - The name to assign to the new view.
 * @param {Object} params.template - The template details.
 * @param {string} params.template.name - The name of the template.
 * @param {string} params.template.selector - The CSS selector for the template.
 * @param {string} params.template.slug - The slug of the template.
 * @param {string} params.template.container - The CSS selector for the container to check.
 * @param {string} params.template.contains - Optional CSS selector for specific content check.
 */
async function createView(page, { formTitle, viewName, template }) {
  await page.waitForSelector('text=New View', { state: 'visible' });
  await page.click('text=New View');
  await selectGravityFormByTitle(page, formTitle);

  await page.fill('#title', viewName);

  await page.waitForSelector('#gravityview_select_template', { state: 'visible' });
  await page.waitForSelector('.gv-view-types-module', { state: 'visible' });

  const templateSelector = await page.$(template.selector);
  const isPlaceholder = await templateSelector.evaluate(element =>
      element.classList.contains('gv-view-template-placeholder')
  );

  if (isPlaceholder) {
      throw new Error(`${template.name} template not found.`);
  }

  const selectButtonLocator = page.locator(`a.gv_select_template[data-templateid="${template.slug}"]`);
  await templateSelector.hover();
  await page.dispatchEvent(template.selector, 'mouseenter');
  await selectButtonLocator.waitFor({ state: 'visible' });
  await selectButtonLocator.click();

  await page.waitForSelector('#gravityview_settings', { state: 'visible' });

  const checkbox = page.locator('#gravityview_se_show_only_approved');
  if (await checkbox.isVisible()) {
      await checkbox.uncheck();
  }

}

/**
 * Helper function to publish a GravityView.
 *
 * @param {import('playwright').Page} page - The Playwright page object.
 */
async function publishView(page) {
  await Promise.all([
      page.click('#publish'),
      page.waitForURL(/\/wp-admin\/post\.php\?post=\d+&action=edit/)
  ]);

  await page.waitForSelector('.notice-success');
  const successMessage = await page.textContent('.notice-success');
  expect(successMessage).toMatch(/View (published|updated)/);

}

/**
 * Helper function to check a newly created GravityView on the front end.
 *
 * @param {import('playwright').Page} page - The Playwright page object.
 * @param {string} permalinkSelector - The CSS selector for the permalink element.
 */
async function checkViewOnFrontEnd(page, permalinkSelector = '#sample-permalink') {
  const viewUrl = await page.$eval(permalinkSelector, (el) => el.href);

  await page.goto(viewUrl);

  await page.waitForURL(viewUrl);
}


module.exports = {
  selectGravityFormByTitle,
  gotoAndEnsureLoggedIn,
  createView,
  publishView,
  checkViewOnFrontEnd,
};
