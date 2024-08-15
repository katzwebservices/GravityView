/**
 * Selects a Gravity Form from the dropdown by matching the form title.
 * Throws an error if the form title is not found.
 *
 * @param {object} page - The Playwright page object.
 * @param {string} formTitle - The title of the Gravity Form to select.
 * @throws Will throw an error if the form title is not found.
 */
async function selectGravityFormByTitle(page, formTitle) {
  const formSelector = '#gravityview_form_id';

  await page.waitForSelector(formSelector);

  const optionValue = await page.evaluate(
    ({ formTitle, selector }) => {
      const select = document.querySelector(selector);
      const options = Array.from(select.options);
      const lowerCaseFormTitle = formTitle.toLowerCase();
      const option = options.find((opt) =>
        opt.textContent.trim().toLowerCase().startsWith(lowerCaseFormTitle)
      );
      return option ? option.value : '';
    },
    { formTitle, selector: formSelector }
  );

  if (optionValue) {
    await page.selectOption(formSelector, optionValue);
  } else {
    throw new Error(`Form with title "${formTitle}" not found.`);
  }
}

module.exports = {
  selectGravityFormByTitle,
};
