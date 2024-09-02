import { test, expect } from '@playwright/test';
import { checkViewOnFrontEnd, createView, gotoAndEnsureLoggedIn, publishView, templates } from '../../helpers/test-helpers';

test('Verify Hide Empty Fields Setting', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);

    await createView(page, { formTitle: 'Has Empty Field', viewName: 'Hide Empty Fields Setting Test', template: templates[0] });
    await publishView(page);
    await checkViewOnFrontEnd(page);

    const thead = await page.locator('table.gv-table-view thead');
    const thElements = await thead.locator('th');
    await expect(thElements).toHaveCount(1);
});