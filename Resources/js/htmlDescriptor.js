document.addEventListener('DOMContentLoaded', function() {



  let prev = null;

  Array.from(document.getElementsByTagName('article')).reverse().forEach(function (article) {
    const dedupId = article.dataset.dedupId;
    if (dedupId === prev) {
      article.getElementsByTagName('header')[0].classList.add('hidden');
    }
    prev = dedupId;
  });


  let lastId = Array.from(document.getElementsByTagName('article')).reverse()[0].dataset.dedupId;

  const params = new Proxy(new URLSearchParams(window.location.search), {
    get: (searchParams, prop) => searchParams.get(prop),
  });
// Get the value of "some_key" in eg "https://example.com/?some_key=some_value"
  let hideFrom = params.s;

  document.getElementById('clearBtn').href = "?s=" + lastId;
  document.getElementById('refreshBtn').href = "?s=" + hideFrom;



  let hideRest = false;
  Array.from(document.getElementsByTagName('article')).reverse().forEach(function (article) {
    const dedupId = article.dataset.dedupId;
    if (hideRest || dedupId === hideFrom) {
      article.parentElement.removeChild(article);
      hideRest = true;
    }
  });




  // AJ - so dumps from same dump call are put together
  prev = null;
  let prevParent = null;
  Array.from(document.getElementsByTagName('p')).reverse().forEach(function (article) {
    const dedupId = article.dataset.dedupId;
    if (dedupId === prev) {
      article.classList.add('hidden');
      // move the pre

      article.parentElement.parentElement.parentElement.removeChild(article.parentElement.parentElement);

      prevParent.appendChild(  article.parentElement.getElementsByTagName('pre')[0]    );


    } else {
      prevParent = article.parentElement;
    }
    prev = dedupId;
  });


  document.body.style.visibility = 'visible';

});
