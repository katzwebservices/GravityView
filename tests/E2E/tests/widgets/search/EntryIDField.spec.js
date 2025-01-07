import { test, expect } from "@playwright/test";
import {
	checkViewOnFrontEnd,
	createView,
	gotoAndEnsureLoggedIn,
	publishView,
	templates,
} from "../../../helpers/test-helpers";

/**
 * Verify that the Entry ID Search Field filters and displays the correct entry.
 */
test("Entry ID Field", async ({ page }, testInfo) => {
	await gotoAndEnsureLoggedIn(page, testInfo);
	await createView(page, {
		formTitle: "A Simple Form",
		viewName: "Entry ID Field Test",
		template: templates[0],
	});
	await page
		.getByRole("button", { name: "Configure Search Bar Settings" })
		.click();
	await page
		.getByRole("cell", { name: "Search Everything" })
		.getByRole("combobox")
		.selectOption("entry_id");
	await page
		.locator(".ui-dialog")
		.getByRole("button", { name: "Close", exact: true })
		.click();
	await publishView(page);
	await checkViewOnFrontEnd(page);
	const link = page.getByRole("link", { name: "Bob" });
	const url = await link.getAttribute("href");
	const params = new URLSearchParams(url);
	const entryId = params.get("entry");
	await page.getByLabel("Entry ID").fill(entryId);
	await page.getByRole("button", { name: "Search" }).click();
	const bob = page.getByRole("cell", { name: "Bob", exact: true });
	const charlie = page.getByRole("cell", { name: "Charlie", exact: true });
	await expect(bob).toBeVisible();
	await expect(charlie).not.toBeVisible();
});
