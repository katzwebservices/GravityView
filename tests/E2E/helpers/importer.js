import { wpLogin } from './wp-login';
import fs from 'fs/promises';
import path from 'path';
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const storageState = path.join(__dirname, '../setup/.state.json');
const url = process.env.URL;
const formsDataDir = path.join(__dirname, '../../data/forms');
const importedFormsFile = path.join(__dirname, '../setup/.imported-forms.json');

/**
 * Import multiple GravityForms data files into WordPress at once.
 *
 * @param {object} page - Playwright page object.
 * @param {Array<string>} formsToImport - Optional list of form filenames (without .json extension) to import.
 */
export async function importGravityFormsData(page, formsToImport = []) {
  let importedForms = {};

  try {
    const data = await fs.readFile(importedFormsFile, 'utf8');
    importedForms = JSON.parse(data);
  } catch (error) {
    if (error.code !== 'ENOENT') {
      console.error('Error reading imported forms file:', error);
      throw error;
    }
  }

  let formFiles = await fs.readdir(formsDataDir);

  if (formsToImport.length > 0) {
    formFiles = formFiles.filter((formFile) =>
      formsToImport.includes(path.basename(formFile, '.json'))
    );
  }

  const filesToImport = formFiles.filter(
    (formFile) => !importedForms[formFile]
  );

  if (filesToImport.length > 0) {
    console.log(`Importing forms: ${filesToImport.join(', ')}`);

    await wpLogin({ page, stateFile: storageState });

    await page.goto(
      `${url}/wp-admin/admin.php?page=gf_export&subview=import_form`
    );

    await page.waitForSelector('input[type="file"]');

    const input = await page.$('input[type="file"]');
    const filesToUpload = filesToImport.map((formFile) =>
      path.join(formsDataDir, formFile)
    );
    await input.setInputFiles(filesToUpload);

    await page.click('input[type="submit"]');

    await page.waitForSelector('#message');
    const messageClass = await page.getAttribute('#message', 'class');
    expect(messageClass).toContain('success');

    filesToImport.forEach((formFile) => {
      importedForms[formFile] = true;
    });
    await fs.writeFile(
      importedFormsFile,
      JSON.stringify(importedForms, null, 2)
    );
  } else {
    console.log('All forms have already been imported.');
  }
}
