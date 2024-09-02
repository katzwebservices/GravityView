import { test, expect, chromium } from '@playwright/test';
import { checkViewOnFrontEnd, countTableEntries, createView, gotoAndEnsureLoggedIn, publishView, templates } from '../helpers/test-helpers';

const filteredTemplates = templates.filter(template => template.name !== 'List');

test.describe('GravityView - Number of entries per page', () => {
    let browser;
    let context;
    let page;

    test.beforeAll(async () => {
        browser = await chromium.launch();
        context = await browser.newContext();
        page = await context.newPage();
        await gotoAndEnsureLoggedIn(page);
    });

    test.afterAll(async () => {
        await page.close();
        await context.close();
        await browser.close();
    });

    test('should correctly paginate entries for each template', async () => {

        const formTitle = 'Event Registration';

        for (const template of filteredTemplates) {
            const viewName = `${template.name} View — Entries Per Page Test`;

            await createView(page, { formTitle, viewName, template });

            await page.locator('#gravityview_settings div').getByRole('link', { name: 'Multiple Entries' }).click();

            const testCases = [30, 5, -1];
            for (const entriesPerPage of testCases) {
                await page.getByLabel('Number of entries per page').fill(entriesPerPage.toString());
                
                await publishView(page);
                
                await checkViewOnFrontEnd(page);

                if (entriesPerPage > 0) {
                    const entryCount = await countTableEntries(page);
                    expect(entryCount).toBeLessThanOrEqual(entriesPerPage);
                    await page.getByRole('menuitem', { name: 'GravityView' }).hover()
                    await page.getByRole('menuitem', { name: 'Edit View' }).click();
                    await page.waitForURL(/\/wp-admin\/post\.php\?post=\d+&action=edit/)

                }
                else {
                    const entryCount = await countTableEntries(page);
                    expect(entryCount).toBeGreaterThan(25); 
                }
            }
        }
    });
});