import $ from 'jquery';
import Sortable from '@typo3/core/Contrib/sortablejs.js';
import Modal from '@typo3/backend/modal.js';
import Severity from '@typo3/backend/severity.js';
import Icons from '@typo3/backend/icons.js';
import Notification from "@typo3/backend/notification.js";

const getIcon = (identifier, size = Icons.sizes.small) => {
  return Icons.getIcon(identifier, size);
};

const compareArrays = (a, b) => {
  return JSON.stringify(a) === JSON.stringify(b);
};

const getDataIds = (container, dataAttribute) => {
  return $(container)
    .children('[data-' + dataAttribute + ']')
    .map(function () {
      return $(this).data(dataAttribute);
    })
    .get();
}

function initAccordion(headerSelector, collapseOthers = false) {
  $(headerSelector).on('click', function (e) {
    const $header = $(this);
    const $content = $header.next('.ui-accordion-content');

    if (collapseOthers) {
      $(headerSelector)
        .not($header).removeClass('active')
        .not($header).next('.ui-accordion-content').slideUp(200);
    }

    $header.toggleClass('active');
    $content.slideToggle(200);
  });
}

function createLink({ href, title, icon, onClick, target = '', marginLeft = '5px' }) {
  const link = document.createElement('a');
  link.href = href;
  link.title = title;
  link.target = target;
  link.style.marginLeft = marginLeft;

  if (onClick) {
    link.addEventListener('click', onClick);
  }

  getIcon(icon).then(iconHtml => {
    link.insertAdjacentHTML('beforeend', iconHtml);
  });

  return link;
}

function openFormPagesModal(button) {
  const $button = $(button);
  const formUid = $button.data('form-uid');
  const formTitle = $button.parent().siblings('h5').text();
  const modalTitle = `${TYPO3.lang['view.formId']} ${formUid} - ${formTitle}`;
  const ajaxUrl = TYPO3.settings.ajaxUrls['powermail_pages'] + '&formUid=' + formUid;

  Modal.advanced({
    title: modalTitle,
    content: '<p>Loading pages...</p>',
    size: Modal.sizes.medium,
    staticBackdrop: true,
    severity: Severity.info,
    callback: function (modalContainer) {
      const modalBody = modalContainer.querySelector('.modal-body');

      fetch(ajaxUrl)
        .then(response => response.json())
        .then(data => {
          modalBody.innerHTML = '';

          if (!data.pages || data.pages.length === 0) {
            modalBody.innerHTML = `<p>${TYPO3.lang['view.noPageFound']}</p>`;
            return;
          }

          const heading = document.createElement('h1');
          heading.textContent = TYPO3.lang['view.relatedPages'];
          modalBody.appendChild(heading);

          const list = document.createElement('ul');

          data.pages.forEach(page => {
            const li = document.createElement('li');
            li.textContent = `${page.title} (${TYPO3.lang['view.pageUid']} ${page.uid})`;

            // Edit Link
            const editUrl = top.TYPO3.settings.FormEngine.moduleUrl
              + `&edit[tt_content][${page.contentUid}]=edit`
              + `&returnUrl=${encodeURIComponent(window.location.href)}`;

            const editLink = createLink({
              href: '#',
              title: page.title,
              icon: 'actions-open',
              onClick: (e) => {
                e.preventDefault();
                window.open(editUrl, 'EditRecordWindow', 'width=1200,height=800,resizable=yes,scrollbars=yes');
              }
            });

            // Show Link
            const showLink = createLink({
              href: page.url,
              title: page.title,
              icon: 'actions-eye',
              target: '_blank'
            });

            li.appendChild(editLink);
            li.appendChild(showLink);

            list.appendChild(li);
          });

          modalBody.appendChild(list);
        })
        .catch(error => {
          modalBody.innerHTML = `<p>Error loading pages: ${error}</p>`;
        });
    }
  });
}

function initSortable(selector, handleSelector, ajaxUrl, parentAttribute, dataAttribute, group = null) {
  $(selector).each(function () {
    let sortedIdsList = [];
    let parentUid = $(this).data(parentAttribute);

    new Sortable(this, {
      handle: handleSelector,
      group: group,
      animation: 150,
      ghostClass: 'ui-state-highlight',
      onStart: function (evt) {
        sortedIdsList = getDataIds(evt.from, dataAttribute);
      },
      onEnd: function (evt) {
        const movedFieldUid = $(evt.item).data(dataAttribute);
        const sortedIds = getDataIds(evt.to, dataAttribute);

        if(compareArrays(sortedIds, sortedIdsList)) {
          return;
        }

        // Detect if parent has changed
        const targetParentUid = $(evt.to).data(parentAttribute);

        if (parentUid !== targetParentUid) {
          parentUid = targetParentUid;
        }

        if (ajaxUrl) {
          fetch(ajaxUrl, {
            method: 'POST', headers: {
              'Content-Type': 'application/json',
            }, body: JSON.stringify({
              [parentAttribute]: parentUid,
              movedFieldUid,
              sortedIds
            })
          })
            .then(response => response.json())
            .then(data => {
              // Update fields count on each page
              [evt.from, evt.to].forEach(container => {
                const count = $(container).children('[data-field-uid]').length;
                $(container)
                  .parent()
                  .siblings()
                  .find('h6 span')
                  .text(count);
              });

              Notification.success('Success', data.message, 1);
            })
            .catch(error => {
              Notification.error('Error', error.message, 1);
            });
        }
      },
    });
  });
}

$(function () {

  // Constants
  const $formHeader = $('.form-header');
  const $pageHeader = $('.page-header');

  // Initialize the accordions
  initAccordion($formHeader, true);
  initAccordion($pageHeader, false);

  // Initialize view details buttons
  $formHeader.find('button').on('click', function (e) {
    e.stopPropagation();
    openFormPagesModal(this);
  });

  $('.form-header a, .page-header a').on('click', function (e) {
    e.stopPropagation();
  });

  // Initialize page sorting
  initSortable('.sortable-pages', '.page-header', TYPO3.settings.ajaxUrls['powermail_move'], 'form-uid', 'page-uid');

  // Initialize field sorting
  initSortable('.sortable-fields', null, TYPO3.settings.ajaxUrls['powermail_move'], 'page-uid', 'field-uid', 'fields');
});
