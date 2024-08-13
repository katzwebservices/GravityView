import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { wpLogin } from '../helpers/wp-login';
import path from 'path';
import fs from 'fs/promises';
import { importGravityFormsData } from '../helpers/importer';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const storageState = path.join(__dirname, '../setup/.state.json');
const url = process.env.URL;

test.describe('GravityView View Creation', () => {
  test.beforeEach(async ({ page }) => {
    try {
      await fs.access(storageState);
    } catch (error) {
      console.error('State file does not exist:', error);
      throw error;
    }

    await wpLogin({ page, stateFile: storageState });
  });

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
});
