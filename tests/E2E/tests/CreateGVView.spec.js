import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { importGravityFormsData } from '../helpers/importer';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const url = process.env.URL;

test.describe('GravityView View Creation', () => {
  test('Create a new GravityView view', async ({ page }) => {
    await page.goto(`${url}/wp-admin/edit.php?post_type=gravityview`);
    await page.waitForSelector('text=New View', { state: 'visible' });
    await page.click('text=New View');

    const form = {
      filename: 'simple',
      title: 'A Simple Form',
    };

    await importGravityFormsData(page, [form.filename]);

    await page.waitForSelector('#gravityview_form_id');

    const optionValue = await page.evaluate((formTitle) => {
      const select = document.querySelector('#gravityview_form_id');
      const options = Array.from(select.options);
      const lowerCaseFormTitle = formTitle.toLowerCase();
      const option = options.find((opt) =>
        opt.textContent.trim().toLowerCase().startsWith(lowerCaseFormTitle)
      );
      return option ? option.value : '';
    }, form.title);

    if (optionValue) {
      await page.selectOption('#gravityview_form_id', optionValue);
    } else {
      throw new Error(`Form with title "${form.title}" not found.`);
    }

    await page.fill('#title', 'Test View');
    await page.click('#publish');

    await page.waitForSelector('.notice-success');
    const successMessage = await page.textContent('.notice-success');
    expect(successMessage).toContain('View published');
  });

  test('Add fields to a GravityView view', async ({ page }) => {
    await page.goto(`${url}/wp-admin/edit.php?post_type=gravityview`);
    const viewSelector = 'a.row-title:has-text("Test View")';

    await page.click(viewSelector);

    await Promise.race([
      page.waitForSelector('#gravityview_select_template', {
        state: 'visible',
      }),
      page.waitForSelector('#gv-view-configuration-tabs', { state: 'visible' }),
    ]);

    if (await page.isVisible('#gravityview_select_template')) {
      console.log('#gravityview_select_template is visible');
      await page.waitForSelector('.gv-view-types-module', { state: 'visible' });
      const tableTemplateSelector = await page.$(
        '.gv-view-types-module:has(h5:text("Table"))'
      );

      if (!tableTemplateSelector) {
        throw new Error('Table template not found.');
      }

      await tableTemplateSelector.hover();
      const selectButtonLocator = page.locator(
        'a.gv_select_template[data-templateid="default_table"]'
      );
      await selectButtonLocator.waitFor({ state: 'visible' });
      await selectButtonLocator.click();

      await page.click('#publish');

      await page.waitForSelector('.notice-success');
      const successMessage = await page.textContent('.notice-success');
      expect(successMessage).toContain('View updated');
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

    const addedFieldSelector =
      '#directory-active-fields .active-drop[data-areaid="directory_table-columns"] .field-id-date_created';
    await page.waitForSelector(addedFieldSelector, { state: 'visible' });

    const fieldCount = await page.$$eval(
      addedFieldSelector,
      (fields) => fields.length
    );
    expect(fieldCount).toBeGreaterThan(0);
  });
});
