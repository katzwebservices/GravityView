import { test, expect } from '@wordpress/e2e-test-utils-playwright';

import { wpLogin } from '../helpers/wp-login';
import path from 'path';
import fs from 'fs/promises';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const storageState = path.join(__dirname, '../setup/.state.json');

const url = process.env.URL;

test('GravityView submenu items are available under the GravityKit menu', async ({
  page,
}) => {
  try {
    await fs.access(storageState);
  } catch (error) {
    console.error('State file does not exist:', error);
    throw error;
  }

  await wpLogin({ page, stateFile: storageState });

  await page.goto(`${url}/wp-admin`);

  const gravityKitMenuSelector = '#toplevel_page__gk_admin_menu';

  await page.waitForSelector(gravityKitMenuSelector);

  const submenus = await page
    .locator(`${gravityKitMenuSelector} .wp-submenu a`)
    .allTextContents();

  const submenuTitles = submenus.map((item) => item.replace(/\d+$/, '').trim()); // Remove the trailing number as it's a hidden <span class="plugin-count">0</span> element.

  expect(submenuTitles).toContain('All Views');
  expect(submenuTitles).toContain('New View');
  expect(submenuTitles).toContain('Getting Started');
});
