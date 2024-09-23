import { test, expect } from '@playwright/test';
import { checkViewOnFrontEnd, createView, gotoAndEnsureLoggedIn, publishView, templates } from '../../helpers/test-helpers';

test('Verify All Fields Are Displayed Correctly', async ({ page }, testInfo) => {
    await gotoAndEnsureLoggedIn(page, testInfo);
    
    await createView(page, { formTitle: 'User Details', viewName: 'Verify All Fields Display', template: templates[0] });

    await page.locator('#gravityview_settings div').getByRole('link', { name: 'Multiple Entries' }).click();

    await publishView(page);

    await checkViewOnFrontEnd(page);

    await expect(page.getByText('Alice Smith')).toBeVisible();
    await expect(page.getByText('Bob Johnson')).toBeVisible();

    await expect(page.getByText('alice@example.com')).toBeVisible();
    await expect(page.getByText('bob@example.com')).toBeVisible();

    await expect(page.getByText('35')).toBeVisible();
    await expect(page.getByText('45')).toBeVisible();

    await expect(page.getByText('06/15/1994')).toBeVisible();
    await expect(page.getByText('08/22/1979')).toBeVisible();

    await expect(page.getByText('Red')).toBeVisible();
    await expect(page.getByText('Blue')).toBeVisible();
});
