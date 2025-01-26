import { test, expect } from "@playwright/test";
import {
	checkViewOnFrontEnd,
	createView,
	gotoAndEnsureLoggedIn,
	publishView,
	templates,
} from "../../helpers/test-helpers";

/**
 * Verifies that custom JavaScript code is executed correctly and affects the behavior of the front-end view as intended.
 */
test("Verify Redirect After Deleting", async ({ page }, testInfo) => {
	const customURL = "http://example.com/";

	await test.step("Log in and navigate to the appropriate page", async () => {
		await gotoAndEnsureLoggedIn(page, testInfo);
	});

	await test.step("Create a new View with a specific form and template", async () => {
		await createView(page, {
			formTitle: "Redundant Favorite Color",
			viewName: "Verify Redirect After Deleting Test",
			template: templates[0],
		});
	});

	await test.step("Configure redirect URL after deleting an entry", async () => {
		await page
			.locator("#gravityview_settings div")
			.getByRole("link", { name: "Delete Entry" })
			.click();
		await page
			.getByLabel("Redirect After Deleting")
			.selectOption({ label: "Redirect to URL" });
		await page.getByLabel("Delete Entry Redirect URL").fill(customURL);
		await publishView(page);
	});

	await test.step("Check View functionality on the front end", async () => {
		await checkViewOnFrontEnd(page);
	});

	await test.step("Delete an entry and verify redirection", async () => {
		await page
			.locator(".gv-table-view tbody tr:nth-child(1)")
			.getByRole("link")
			.click();
		await page.getByRole("link", { name: "Edit Entry" }).click();
		page.on("dialog", (dialog) => dialog.accept());
		await page.getByRole("link", { name: "Delete", exact: true }).click();
		await page.waitForURL(customURL);
		expect(page.url()).toBe(customURL);
	});
});
