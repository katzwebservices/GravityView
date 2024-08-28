import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { selectGravityFormByTitle, gotoAndEnsureLoggedIn } from '../../helpers/test-helpers';

const url = process.env.URL;

test.describe('GravityView Template Selection', () => {
  const templates = [
    {
      name: 'Table',
      slug: 'default_table',
      selector: '.gv-view-types-module:has(h5:text("Table"))',
      container: '.gv-table-container',
      contains: 'table.gv-table-view',
    },
    {
      name: 'List',
      slug: 'default_list',
      selector: '.gv-view-types-module:has(h5:text("List"))',
      container: '.gv-list-container',
      contains: 'ul.gv-list-view',
    },
    {
      name: 'DataTables Table',
      slug: 'datatables_table',
      selector: '.gv-view-types-module:has(h5:text("DataTables Table"))',
      container: '.gv-datatables-container',
      contains: 'table.dataTable',
    },
  ];

  const form = {
    filename: 'simple',
    title: 'A Simple Form',
  };

  for (const template of templates) {
    test(`Verify GravityView template: ${template.name}`, async ({ page }, testInfo) => {
      await gotoAndEnsureLoggedIn(page, testInfo);
      
      await page.waitForSelector('text=New View', { state: 'visible' });

      await page.click('text=New View');

      await selectGravityFormByTitle(page, form.title);

      await page.fill('#title', `Test View - ${template.name}`);

      page.waitForSelector('#gravityview_select_template', {
        state: 'visible',
      });

      await page.waitForSelector('.gv-view-types-module', {
        state: 'visible',
      });

      const templateSelector = await page.$(template.selector);

      const isPlaceholder = await templateSelector.evaluate(element =>
        element.classList.contains('gv-view-template-placeholder')
      );

      testInfo.skip(isPlaceholder, `${template.name} template not found.`);
      
      const selectButtonLocator = page.locator(
        `a.gv_select_template[data-templateid="${template.slug}"]`
      );
      await templateSelector.hover();
      await page.dispatchEvent(template.selector, 'mouseenter');
      await selectButtonLocator.waitFor({ state: 'visible' });
      await selectButtonLocator.click();

      await page.waitForSelector('#gravityview_settings', { state: 'visible' });

      const checkbox = page.locator('#gravityview_se_show_only_approved');

      await checkbox.isVisible() && checkbox.uncheck();

      await Promise.all([
        page.click('#publish'),
        page.waitForURL(/\/wp-admin\/post\.php\?post=\d+&action=edit/),
      ]);

      await page.waitForSelector('.notice-success');
      const successMessage = await page.textContent('.notice-success');
      expect(successMessage).toContain('View published');

      const viewUrl = await page.$eval('#sample-permalink', (el) => el.href);
      await page.goto(viewUrl);

      await page.waitForURL(viewUrl);
      const containerExists = await page.locator(template.container).isVisible();
      expect(containerExists).toBeTruthy();

      // Check below is simplified for List View since it has no default fields.
      if (template.contains && template.slug !== 'default_list') {
        const elementExists = await page.locator(`${template.container} ${template.contains}`).isVisible();
        expect(elementExists).toBeTruthy();
      }

    });
  }
});
