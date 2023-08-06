(function () {

  let button = document.querySelector('#contactForm button');
  if (button) {
    button.addEventListener('click', function (event) {
      event.preventDefault();

      const form = document.getElementById('contactForm');
      const formData = new FormData(form);
      fetch('contact', {
        method: 'POST',
        body: formData
      })
      .then(function (res) {
        return res.text();
      })
      .then(function (text) {
        console.log(text);
        const element = document.createElement('div');
        element.classList.add('alert', 'alert-primary');
        element.innerHTML = `Server replied: ${text}`;
        
        const replies = document.getElementById('replies');
        if (replies) {
          replies.appendChild(element);
        }
      })
      .catch(function (err) {
        console.error(err);
      });
    });
  };

})();