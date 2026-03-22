(function () {
  'use strict';

  var appSelector = '#glsr-admin-app';
  var tabsSelector = '.nav-tab-wrapper a.nav-tab, .glsr-subsubsub a';
  var activeRequestId = 0;

  function findAppRoot(doc) {
    return (doc || document).querySelector(appSelector);
  }

  function setLoading(root, loading) {
    if (!root) {
      return;
    }
    root.classList.toggle('is-loading', !!loading);
  }

  function replaceSectionFromDocument(targetDoc) {
    var currentRoot = findAppRoot(document);
    var incomingRoot = findAppRoot(targetDoc);
    if (!currentRoot || !incomingRoot) {
      return false;
    }

    var currentTabs = currentRoot.querySelector('.nav-tab-wrapper');
    var incomingTabs = incomingRoot.querySelector('.nav-tab-wrapper');
    if (currentTabs && incomingTabs) {
      currentTabs.outerHTML = incomingTabs.outerHTML;
    }

    var currentSubtabs = currentRoot.querySelector('.glsr-subsubsub');
    var incomingSubtabs = incomingRoot.querySelector('.glsr-subsubsub');
    if (currentSubtabs && incomingSubtabs) {
      currentSubtabs.outerHTML = incomingSubtabs.outerHTML;
    } else if (currentSubtabs && !incomingSubtabs) {
      currentSubtabs.remove();
    } else if (!currentSubtabs && incomingSubtabs) {
      var tabsEl = currentRoot.querySelector('.nav-tab-wrapper');
      if (tabsEl) {
        tabsEl.insertAdjacentElement('afterend', incomingSubtabs);
      }
    }

    var currentTitle = currentRoot.querySelector('.page-title');
    var incomingTitle = incomingRoot.querySelector('.page-title');
    if (currentTitle && incomingTitle) {
      currentTitle.textContent = incomingTitle.textContent;
    }

    var currentContent = currentRoot.querySelector('#glsr-tab-content');
    var incomingContent = incomingRoot.querySelector('#glsr-tab-content');
    if (currentContent && incomingContent) {
      currentContent.outerHTML = incomingContent.outerHTML;
      return true;
    }

    return false;
  }

  function parseDocumentFromHtml(html) {
    var parser = new DOMParser();
    return parser.parseFromString(html, 'text/html');
  }

  function navigate(url, push) {
    var root = findAppRoot(document);
    if (!root) {
      return;
    }

    if (url === window.location.href && push) {
      return;
    }

    activeRequestId += 1;
    var requestId = activeRequestId;

    setLoading(root, true);

    fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Request failed: ' + response.status);
        }
        return response.text();
      })
      .then(function (html) {
        if (requestId !== activeRequestId) {
          return;
        }

        var nextDoc = parseDocumentFromHtml(html);
        var replaced = replaceSectionFromDocument(nextDoc);

        if (!replaced) {
          window.location.assign(url);
          return;
        }

        if (push) {
          window.history.pushState({ glsr: true, url: url }, '', url);
        }

        var content = root.querySelector('#glsr-tab-content');
        if (content) {
          content.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      })
      .catch(function () {
        if (requestId !== activeRequestId) {
          return;
        }
        window.location.assign(url);
      })
      .finally(function () {
        if (requestId === activeRequestId) {
          setLoading(root, false);
        }
      });
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest(tabsSelector);
    if (!link) {
      return;
    }

    var appRoot = findAppRoot(document);
    if (!appRoot || !appRoot.contains(link)) {
      return;
    }

    if (!link.href) {
      return;
    }

    event.preventDefault();
    navigate(link.href, true);
  });

  window.addEventListener('popstate', function () {
    if (!window.history.state || window.history.state.glsr !== true) {
      return;
    }

    var appRoot = findAppRoot(document);
    if (!appRoot) {
      return;
    }

    navigate(window.history.state.url || window.location.href, false);
  });

  if (!window.history.state || window.history.state.glsr !== true) {
    window.history.replaceState({ glsr: true, url: window.location.href }, '', window.location.href);
  }
})();
