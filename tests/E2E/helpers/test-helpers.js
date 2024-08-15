/**
 * Helper function to get the value of an option from a select element by matching the title.
 *
 * @param {object} page - The Playwright page object.
 * @param {string} formTitle - The title of the form to search for in the select options.
 * @returns {Promise<string>} - The value of the matched option, or an empty string if not found.
 */
async function getOptionValueByFormTitle(page, formTitle) {
  return await page.evaluate((formTitle) => {
    const select = document.querySelector('#gravityview_form_id');
    const options = Array.from(select.options);
    const lowerCaseFormTitle = formTitle.toLowerCase();
    const option = options.find((opt) =>
      opt.textContent.trim().toLowerCase().startsWith(lowerCaseFormTitle)
    );
    return option ? option.value : '';
  }, formTitle);
}

module.exports = {
  getOptionValueByFormTitle,
};
