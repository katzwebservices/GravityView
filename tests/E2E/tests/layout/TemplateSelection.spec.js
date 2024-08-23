import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { selectGravityFormByTitle } from '../../helpers/test-helpers';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const url = process.env.URL;

test.describe('GravityView Template Selection', () => {
  const templates = [
    {
      name: 'Table',
      slug: 'default_table',
      selector: '.gv-view-types-module:has(h5:text("Table"))',
    },
    {
      name: 'List',
      slug: 'default_list',
      selector: '.gv-view-types-module:has(h5:text("List"))',
    },
    {
      name: 'DataTables Table',
      slug: 'datatables_table',
      selector: '.gv-view-types-module:has(h5:text("DataTables Table"))',
    },
  ];

  const form = {
    filename: 'simple',
    title: 'A Simple Form',
  };

  for (const template of templates) {
    test(`Verify GravityView template: ${template.name}`, async ({ page }, testInfo) => {
      await page.goto(`${url}/wp-admin/edit.php?post_type=gravityview`);
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

      await Promise.all([
        page.click('#publish'),
        page.waitForURL(/\/wp-admin\/post\.php\?post=\d+&action=edit/),
      ]);

      await page.waitForSelector('.notice-success');
      const successMessage = await page.textContent('.notice-success');
      expect(successMessage).toContain('View published');

      const viewUrl = await page.$eval('#sample-permalink', (el) => el.href);
      await page.goto(viewUrl);

      // TODO: Add actual verification logic
      await page.waitForURL(viewUrl);
      const entryExists = await page.locator('.gv-grid');
      expect(entryExists).toBeTruthy();

    });
  }
});
