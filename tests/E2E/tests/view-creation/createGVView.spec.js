import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import {
  createView,
  gotoAndEnsureLoggedIn,
  publishView,
  templates,
} from '../../helpers/test-helpers';

const url = process.env.URL;

test.describe('GravityView View Creation', () => {
  test('Create a new GravityView view', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);

    await createView(page, {formTitle: 'A Simple Form', viewName: 'Test View', template: templates[0]});

    await publishView(page);
  });

  test('Add fields to a GravityView View', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);
    const viewSelector = 'a.row-title:has-text("Test View")';
    await page.click(viewSelector);

    await Promise.race([
      page.waitForSelector('#gravityview_select_template', {
        state: 'visible',
      }),
      page.waitForSelector('#gv-view-configuration-tabs', { state: 'visible' }),
    ]);

    if (await page.isVisible('#gravityview_select_template')) {
      await page.waitForSelector('.gv-view-types-module', { state: 'visible' });
      const tableTemplateSelector = await page.$('div.gv-view-types-module:has(a.gv_select_template[href="#gv_select_template"][data-templateid="default_table"])');

      if (!tableTemplateSelector) {
        throw new Error('Table template not found.');
      }

      await tableTemplateSelector.hover();
      const selectButtonLocator = page.locator(
        'a.gv_select_template[data-templateid="default_table"]'
      );
      await selectButtonLocator.waitFor({ state: 'visible' });
      await selectButtonLocator.click();

      await publishView(page);
    }

    await page.waitForSelector('.gv-fields');
    await page.click('#directory-active-fields a.gv-add-field');

    const fieldSelector = '.gravityview-item-picker-tooltip';
    await page.waitForSelector(fieldSelector, { state: 'visible' });

    const addFieldButton =
      '.gravityview-item-picker-tooltip div[data-fieldid="date_created"] .gv-add-field';
    await page.waitForSelector(addFieldButton, { state: 'visible' });

    await page.click(
      '.gravityview-item-picker-tooltip .gv-items-picker-container > div[data-fieldid="date_created"]'
    );

    const addedFieldSelector = page.locator('#directory-active-fields').getByTitle('Field: Date Created\nThe date the entry was created.\nForm ID:').locator('span');

    await expect(addedFieldSelector).toBeVisible();

  });
});
