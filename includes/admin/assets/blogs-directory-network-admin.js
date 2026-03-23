(function () {
  'use strict';

  var rootSelector = '.bd-modern-admin';
  var tabsSelector = '.bd-tab-nav a.nav-tab';
  var panelSelector = '#bd-settings-panel';
  var activeRequestId = 0;

  function findRoot(doc) {
    return (doc || document).querySelector(rootSelector);
  }

  function setLoading(root, loading) {
    if (!root) {
      return;
    }
    root.classList.toggle('is-loading', !!loading);
  }

  function initColorControls(context) {
    var scope = context || document;
    var colorInputs = [
      { input: 'blogs_directory_background_color', preview: 'preview_background_color', text: 'text_background_color' },
      { input: 'blogs_directory_alternate_background_color', preview: 'preview_alternate_background_color', text: 'text_alternate_background_color' },
      { input: 'blogs_directory_background_title_color', preview: 'preview_background_title_color', text: 'text_background_title_color' },
      { input: 'blogs_directory_background_text_color', preview: 'preview_background_text_color', text: 'text_background_text_color' },
      { input: 'blogs_directory_background_link_color', preview: 'preview_background_link_color', text: 'text_background_link_color' },
      { input: 'blogs_directory_alternate_title_color', preview: 'preview_alternate_title_color', text: 'text_alternate_title_color' },
      { input: 'blogs_directory_alternate_text_color', preview: 'preview_alternate_text_color', text: 'text_alternate_text_color' },
      { input: 'blogs_directory_alternate_link_color', preview: 'preview_alternate_link_color', text: 'text_alternate_link_color' },
      { input: 'blogs_directory_border_color', preview: 'preview_border_color', text: 'text_border_color' }
    ];

    colorInputs.forEach(function (item) {
      var input = scope.querySelector('#' + item.input);
      var preview = scope.querySelector('#' + item.preview);
      var text = scope.querySelector('#' + item.text);

      if (!input || !preview || !text) {
        return;
      }

      var onInput = function () {
        var color = input.value;
        text.textContent = color;

        if (preview.dataset.previewType === 'border') {
          preview.style.borderColor = color;
        } else if (preview.dataset.previewType === 'text') {
          preview.style.color = color;
        } else {
          preview.style.backgroundColor = color;
        }
      };

      onInput();
      input.addEventListener('input', onInput);
    });
  }

  function initRecentPostsToggle(context) {
    var scope = context || document;
    var master = scope.querySelector('#blogs_directory_show_recent_posts');
    var rows = scope.querySelectorAll('.bd-recent-posts-detail-row');

    if (!master || !rows.length) {
      return;
    }

    var update = function () {
      rows.forEach(function (row) {
        row.classList.toggle('bd-hidden-row', !master.checked);
      });
    };

    update();
    master.addEventListener('change', update);
  }

  function replaceFromDocument(nextDoc) {
    var currentRoot = findRoot(document);
    var incomingRoot = findRoot(nextDoc);

    if (!currentRoot || !incomingRoot) {
      return false;
    }

    var currentTabs = currentRoot.querySelector('.bd-tab-nav');
    var incomingTabs = incomingRoot.querySelector('.bd-tab-nav');
    if (currentTabs && incomingTabs) {
      currentTabs.outerHTML = incomingTabs.outerHTML;
    }

    var currentPanel = currentRoot.querySelector(panelSelector);
    var incomingPanel = incomingRoot.querySelector(panelSelector);
    if (currentPanel && incomingPanel) {
      currentPanel.outerHTML = incomingPanel.outerHTML;
      initColorControls(currentRoot);
      initRecentPostsToggle(currentRoot);
      return true;
    }

    return false;
  }

  function parseHtml(html) {
    return new DOMParser().parseFromString(html, 'text/html');
  }

  function navigate(url, pushState) {
    var root = findRoot(document);
    if (!root) {
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
          throw new Error('Failed request: ' + response.status);
        }
        return response.text();
      })
      .then(function (html) {
        if (requestId !== activeRequestId) {
          return;
        }

        var nextDoc = parseHtml(html);
        var replaced = replaceFromDocument(nextDoc);

        if (!replaced) {
          window.location.assign(url);
          return;
        }

        if (pushState) {
          window.history.pushState({ bdSettings: true, url: url }, '', url);
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

    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      return;
    }

    if (typeof event.button === 'number' && event.button !== 0) {
      return;
    }

    var root = findRoot(document);
    if (!root || !root.contains(link) || !link.href) {
      return;
    }

    event.preventDefault();
    navigate(link.href, true);
  });

  window.addEventListener('popstate', function () {
    if (!window.history.state || window.history.state.bdSettings !== true) {
      return;
    }

    navigate(window.history.state.url || window.location.href, false);
  });

  if (!window.history.state || window.history.state.bdSettings !== true) {
    window.history.replaceState({ bdSettings: true, url: window.location.href }, '', window.location.href);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      initColorControls(document);
      initRecentPostsToggle(document);
    });
  } else {
    initColorControls(document);
    initRecentPostsToggle(document);
  }
})();
