import { test, expect } from '@playwright/test';
import { gotoAndEnsureLoggedIn, selectGravityFormByTitle } from '../helpers/test-helpers';

test('Verify Lightbox Functionality for Images', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);

    await page.getByText('New View', { exact: true }).click();

    await page.getByLabel('Enter View name here').click();
    await page.getByLabel('Enter View name here').fill('Image Lightbox Test');

    const form = {
        filename: 'complete',
        title: 'Complete',
    };

    await selectGravityFormByTitle(page, form.title);

    await page.waitForSelector('#gravityview_select_template', { state: 'visible' });
    await page.waitForSelector('.gv-view-types-module', { state: 'visible' });

    const tableTemplateSelector = await page.$('div.gv-view-types-module:has(a.gv_select_template[href="#gv_select_template"][data-templateid="default_table"])');
    if (!tableTemplateSelector) {
        throw new Error('Table template not found.');
    }
    await tableTemplateSelector.hover();
    const selectButtonLocator = page.locator('a.gv_select_template[data-templateid="default_table"]');
    await selectButtonLocator.waitFor({ state: 'visible' });
    await selectButtonLocator.click();

    await page.getByLabel('Show only approved entries').setChecked(false);

    await Promise.all([
        page.click('#publish'),
        page.waitForURL(/\/wp-admin\/post\.php\?post=\d+&action=edit/),
    ]);
    await page.waitForSelector('.notice-success');
    const successMessage = await page.textContent('.notice-success');
    expect(successMessage).toContain('View published');

    const viewUrl = await page.$eval('#sample-permalink', (el) => el.href);
    await page.goto(viewUrl);

    const imageLocator = page.locator('.gv-container .gravityview-fancybox').first();
    await expect(imageLocator).toBeVisible();
    
    await imageLocator.click();
    await expect(page.locator('.fancybox__container')).toBeVisible();
});
