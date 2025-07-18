if (!window.Joomla) {
  throw new Error('Joomla API was not properly initialised');
}

const { suffix } = Joomla.getOptions('mod_showcase.vars');
document.querySelectorAll('.mod_showcase').forEach(element => {
  element.innerText += suffix
});
