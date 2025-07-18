if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const { suffix } = Joomla.getOptions('mod_showcase.vars');
document.querySelectorAll('.mod_showcase').forEach(element => {
  element.innerText += suffix;
});

const countUsers = (event) => {
  const nusers = event.target.parentElement.querySelector('span.mod_showcase_nusers');
  Joomla.request({
    url: 'index.php?option=com_ajax&module=showcase&method=count&format=json',
    method: 'GET',
    onSuccess(data) {
      const response = JSON.parse(data);
      if (response.success) {
        nusers.innerText = response.data;
        const confirmation = Joomla.Text._('MOD_SHOWCASE_AJAX_OK').replace('%s', response.data);
        Joomla.renderMessages({ 'info': [confirmation] });
      } else {
        const messages = { 'error': [response.message] };
        Joomla.renderMessages(messages);
      }
    },
    onError(xhr) {
      Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
      const response = JSON.parse(xhr.response);
      Joomla.renderMessages({ 'error': [response.message] }, undefined, true);
    }
  });
};

document.querySelectorAll('.mod_showcase_updateusers').forEach(element => {
  element.addEventListener('click', countUsers);
});
