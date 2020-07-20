import Axios from 'axios';

document.addEventListener('DOMContentLoaded', () => {
  const selector = 'input[name=current-symposium]';
  const checkboxes = document.querySelectorAll(selector);
  checkboxes.forEach((input) => {
    input.addEventListener('click', () => {
      let formData = new FormData;
      formData.append('action', 'simpsymp-set-current-symposium');
      formData.append('termId', input.value);

      // noinspection JSUnresolvedVariable
      Axios.post(ajaxurl, formData).then((response) => {
        if (response.data.success) {
          const allChecked = document.querySelectorAll(selector + ':checked');
          allChecked.forEach((checked) => {
            if (checked.value !== input.value) {
              checked.checked = false;
            }
          });
        } else {
          alert('Unable to set current symposium to the one indicated.');
          input.checked = false;
        }
      });
    });
  });
});
