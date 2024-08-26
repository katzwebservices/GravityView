const { wpLogin } = require('./wp-login');
const path = require('path');

// require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

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
async function gotoAndEnsureLoggedIn(page, testInfo, url = defaultGVAdminURL, stateFile = storageState) {
  await page.goto(url);

  const adminBarSelector = '#wpadminbar';
  const isLoggedIn = await page.$(adminBarSelector);

  if (!isLoggedIn) {
      console.log('User is not logged in. Skipping test...');
      testInfo.skip(!isLoggedIn, 'Login required. Please delete the old state file and try again.');
  }
}

module.exports = {
  selectGravityFormByTitle,
  gotoAndEnsureLoggedIn,
};
