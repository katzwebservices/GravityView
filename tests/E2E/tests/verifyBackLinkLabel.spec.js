import { test, expect } from '@playwright/test';
import { checkViewOnFrontEnd, createView, gotoAndEnsureLoggedIn, publishView, templates } from '../helpers/test-helpers';

test('Verify Back Link Label', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);
    await createView(page, { formTitle: 'Event Registration', viewName: 'Verify Back Link Label Test', template: templates[0] });

    await page.locator('#gravityview_settings div').getByRole('link', { name: 'Single Entry' }).click();
    const customMessage = "Return to the Scene of the Crime";
    await page.getByPlaceholder('Go back').fill(customMessage);
    await publishView(page);
    await checkViewOnFrontEnd(page);
    await page.getByRole('link', { name: 'John Doe' }).click();
    await expect(page.getByRole('link', { name: customMessage })).toBeVisible();

});