import { test, expect } from '@wordpress/e2e-test-utils-playwright';

require('dotenv').config({ path: `${process.env.INIT_CWD}/.env` });

const url = process.env.URL;

test('GravityView submenu items are available under the GravityKit menu', async ({
  page,
}) => {
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
