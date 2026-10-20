/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 716:
/***/ (() => {

// extracted by mini-css-extract-plugin

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be in strict mode.
(() => {
"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _admin_less__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(716);
/* harmony import */ var _admin_less__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_admin_less__WEBPACK_IMPORTED_MODULE_0__);
/* global ddwcwlAdminObj, tinymce */




var _ref = window.ddwcwlAdminObj || {},
    confirmDelete = _ref.confirmDelete,
    confirmNotify = _ref.confirmNotify,
    ajaxUrl = _ref.ajaxUrl,
    previewNonce = _ref.previewNonce,
    previewTitle = _ref.previewTitle,
    close = _ref.close,
    sending = _ref.sending,
    error = _ref.error;
/**
 * Toggle an inline admin form (add subscriber).
 *
 * @param {string} id Form ID.
 */


var toggleForm = function toggleForm(id) {
  var form = document.getElementById(id);

  if (!form) {
    return;
  }

  form.hidden = !form.hidden;

  if (!form.hidden) {
    var input = form.querySelector('input:not([type="hidden"])');

    if (input) {
      input.focus();
    }
  }
};
/**
 * Insert a placeholder into the last focused subject, heading or content field.
 */


var lastField = null;
document.addEventListener('focusin', function (event) {
  if (event.target.matches('.ddwcwl-placeholder-target, .wp-editor-area')) {
    lastField = event.target;
  }
});

var insertPlaceholder = function insertPlaceholder(placeholder) {
  var _field$selectionStart, _field$selectionEnd;

  var editorId = document.querySelector('.wp-editor-area') ? document.querySelector('.wp-editor-area').id : '';
  var editor = window.tinymce && editorId ? tinymce.get(editorId) : null; // Visual editor active and no text field picked: insert into the content.

  if ((!lastField || lastField.classList.contains('wp-editor-area')) && editor && !editor.isHidden()) {
    editor.execCommand('mceInsertContent', false, placeholder);
    return;
  }

  var field = lastField || document.querySelector('.wp-editor-area');

  if (!field) {
    return;
  }

  var start = (_field$selectionStart = field.selectionStart) !== null && _field$selectionStart !== void 0 ? _field$selectionStart : field.value.length;
  var end = (_field$selectionEnd = field.selectionEnd) !== null && _field$selectionEnd !== void 0 ? _field$selectionEnd : field.value.length;
  field.value = field.value.slice(0, start) + placeholder + field.value.slice(end);
  field.focus();
  field.setSelectionRange(start + placeholder.length, start + placeholder.length);
};
/**
 * Open the email preview in a dialog.
 *
 * @param {string} url Preview URL.
 */


var openPreview = function openPreview(url) {
  var dialog = document.querySelector('.ddwcwl-preview-dialog');

  if (!dialog) {
    dialog = document.createElement('dialog');
    dialog.className = 'ddwcwl-preview-dialog';
    dialog.innerHTML = '<div class="ddwcwl-preview-head"><strong></strong><button type="button" class="ddwcwl-preview-close"><span class="dashicons dashicons-no-alt" aria-hidden="true"></span></button></div><iframe title=""></iframe>';
    dialog.querySelector('strong').textContent = previewTitle;
    dialog.querySelector('.ddwcwl-preview-close').setAttribute('aria-label', close);
    dialog.querySelector('iframe').title = previewTitle;
    dialog.querySelector('.ddwcwl-preview-close').addEventListener('click', function () {
      return dialog.close();
    });
    dialog.addEventListener('click', function (event) {
      return event.target === dialog && dialog.close();
    });
    document.body.appendChild(dialog);
  }

  dialog.querySelector('iframe').src = url;
  dialog.showModal();
};
/**
 * Send a test email.
 *
 * @param {HTMLElement} toolbar Toolbar element.
 */


var sendTest = function sendTest(toolbar) {
  var button = toolbar.querySelector('.ddwcwl-send-test');
  var result = toolbar.querySelector('.ddwcwl-test-result');
  var input = toolbar.querySelector('input[type="email"]');

  if (!input.checkValidity() || !input.value) {
    input.reportValidity();
    return;
  }

  var data = new FormData();
  data.append('action', 'ddwcwl_send_test_email');
  data.append('nonce', previewNonce);
  data.append('type', toolbar.dataset.type);
  data.append('recipient', input.value);
  button.disabled = true;
  result.className = 'ddwcwl-test-result';
  result.textContent = sending;
  fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    body: data
  }).then(function (response) {
    return response.json();
  }).then(function (_ref2) {
    var success = _ref2.success,
        response = _ref2.data;
    result.classList.add(success ? 'ddwcwl-success' : 'ddwcwl-error');
    result.textContent = response.message || error;
  }).catch(function () {
    result.classList.add('ddwcwl-error');
    result.textContent = error;
  }).finally(function () {
    return button.disabled = false;
  });
};

document.addEventListener('click', function (event) {
  var target = event.target;

  if (target.closest('.ddwcwl-toggle-add')) {
    event.preventDefault();
    toggleForm('ddwcwl-add-subscriber');
    return;
  }

  if (target.closest('.ddwcwl-placeholder')) {
    event.preventDefault();
    insertPlaceholder(target.closest('.ddwcwl-placeholder').dataset.placeholder);
    return;
  }

  if (target.closest('.ddwcwl-preview-email')) {
    event.preventDefault();
    openPreview(target.closest('.ddwcwl-preview-email').dataset.url);
    return;
  }

  if (target.closest('.ddwcwl-send-test')) {
    event.preventDefault();
    sendTest(target.closest('.ddwcwl-email-toolbar'));
    return;
  }

  var link = target.closest('.ddwcwl-confirm-delete, .ddwcwl-confirm-notify');

  if (link && !window.confirm(link.classList.contains('ddwcwl-confirm-delete') ? confirmDelete : confirmNotify)) {
    event.preventDefault();
  }
}); // Enter in the test email field sends the test instead of saving the settings form.

document.addEventListener('keydown', function (event) {
  if ('Enter' === event.key && event.target.closest('.ddwcwl-test-email')) {
    event.preventDefault();
    sendTest(event.target.closest('.ddwcwl-email-toolbar'));
  }
}); // Confirm destructive bulk actions.

document.addEventListener('submit', function (event) {
  var form = event.target;

  if (!form.querySelector('.wp-list-table') || !event.submitter || !/^doaction2?$/.test(event.submitter.id)) {
    return;
  }

  var select = form.querySelector('doaction2' === event.submitter.id ? '#bulk-action-selector-bottom' : '#bulk-action-selector-top');
  var action = select ? select.value : '';

  if (('delete' === action || 'clear' === action) && !window.confirm(confirmDelete)) {
    event.preventDefault();
  } else if (('notify' === action || 'notify_all' === action) && !window.confirm(confirmNotify)) {
    event.preventDefault();
  }
});
})();

var __webpack_export_target__ = this;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uL3NyYy9hZG1pbi9hZG1pbi5sZXNzPzA2OTYiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ydW50aW1lL2NvbXBhdCBnZXQgZGVmYXVsdCBleHBvcnQiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ydW50aW1lL2RlZmluZSBwcm9wZXJ0eSBnZXR0ZXJzIiwid2VicGFjazovL2RldmRpZ2dlcnMtd2FpdGxpc3QtZm9yLXdvb2NvbW1lcmNlL3dlYnBhY2svcnVudGltZS9oYXNPd25Qcm9wZXJ0eSBzaG9ydGhhbmQiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ydW50aW1lL21ha2UgbmFtZXNwYWNlIG9iamVjdCIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uL3NyYy9hZG1pbi9pbmRleC5qcyJdLCJuYW1lcyI6WyJ3aW5kb3ciLCJkZHdjd2xBZG1pbk9iaiIsImNvbmZpcm1EZWxldGUiLCJjb25maXJtTm90aWZ5IiwiYWpheFVybCIsInByZXZpZXdOb25jZSIsInByZXZpZXdUaXRsZSIsImNsb3NlIiwic2VuZGluZyIsImVycm9yIiwidG9nZ2xlRm9ybSIsImlkIiwiZm9ybSIsImRvY3VtZW50IiwiZ2V0RWxlbWVudEJ5SWQiLCJoaWRkZW4iLCJpbnB1dCIsInF1ZXJ5U2VsZWN0b3IiLCJmb2N1cyIsImxhc3RGaWVsZCIsImFkZEV2ZW50TGlzdGVuZXIiLCJldmVudCIsInRhcmdldCIsIm1hdGNoZXMiLCJpbnNlcnRQbGFjZWhvbGRlciIsInBsYWNlaG9sZGVyIiwiZWRpdG9ySWQiLCJlZGl0b3IiLCJ0aW55bWNlIiwiZ2V0IiwiY2xhc3NMaXN0IiwiY29udGFpbnMiLCJpc0hpZGRlbiIsImV4ZWNDb21tYW5kIiwiZmllbGQiLCJzdGFydCIsInNlbGVjdGlvblN0YXJ0IiwidmFsdWUiLCJsZW5ndGgiLCJlbmQiLCJzZWxlY3Rpb25FbmQiLCJzbGljZSIsInNldFNlbGVjdGlvblJhbmdlIiwib3BlblByZXZpZXciLCJ1cmwiLCJkaWFsb2ciLCJjcmVhdGVFbGVtZW50IiwiY2xhc3NOYW1lIiwiaW5uZXJIVE1MIiwidGV4dENvbnRlbnQiLCJzZXRBdHRyaWJ1dGUiLCJ0aXRsZSIsImJvZHkiLCJhcHBlbmRDaGlsZCIsInNyYyIsInNob3dNb2RhbCIsInNlbmRUZXN0IiwidG9vbGJhciIsImJ1dHRvbiIsInJlc3VsdCIsImNoZWNrVmFsaWRpdHkiLCJyZXBvcnRWYWxpZGl0eSIsImRhdGEiLCJGb3JtRGF0YSIsImFwcGVuZCIsImRhdGFzZXQiLCJ0eXBlIiwiZGlzYWJsZWQiLCJmZXRjaCIsIm1ldGhvZCIsImNyZWRlbnRpYWxzIiwidGhlbiIsInJlc3BvbnNlIiwianNvbiIsInN1Y2Nlc3MiLCJhZGQiLCJtZXNzYWdlIiwiY2F0Y2giLCJmaW5hbGx5IiwiY2xvc2VzdCIsInByZXZlbnREZWZhdWx0IiwibGluayIsImNvbmZpcm0iLCJrZXkiLCJzdWJtaXR0ZXIiLCJ0ZXN0Iiwic2VsZWN0IiwiYWN0aW9uIl0sIm1hcHBpbmdzIjoiOzs7Ozs7QUFBQSx1Qzs7Ozs7O1VDQUE7VUFDQTs7VUFFQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTs7VUFFQTtVQUNBOztVQUVBO1VBQ0E7VUFDQTs7Ozs7V0N0QkE7V0FDQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLGdDQUFnQyxZQUFZO1dBQzVDO1dBQ0EsRTs7Ozs7V0NQQTtXQUNBO1dBQ0E7V0FDQTtXQUNBLHdDQUF3Qyx5Q0FBeUM7V0FDakY7V0FDQTtX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YXlELE9BQWIsQ0FBc0IsdUJBQXRCLENBQUYsQ0FBUjtBQUNBO0FBQ0QsQ0FMRCxFLENBT0E7O0FBQ0FsRSxRQUFRLENBQUNPLGdCQUFULENBQTJCLFFBQTNCLEVBQXFDLFVBQUVDLEtBQUYsRUFBYTtBQUNqRCxNQUFNVCxJQUFJLEdBQUdTLEtBQUssQ0FBQ0MsTUFBbkI7O0FBRUEsTUFBSyxDQUFFVixJQUFJLENBQUNLLGFBQUwsQ0FBb0IsZ0JBQXBCLENBQUYsSUFBNEMsQ0FBRUksS0FBSyxDQUFDK0QsU0FBcEQsSUFBaUUsQ0FBRSxlQUFlQyxJQUFmLENBQXFCaEUsS0FBSyxDQUFDK0QsU0FBTixDQUFnQnpFLEVBQXJDLENBQXhFLEVBQW9IO0FBQ25IO0FBQ0E7O0FBRUQsTUFBTTJFLE1BQU0sR0FBRzFFLElBQUksQ0FBQ0ssYUFBTCxDQUFvQixnQkFBZ0JJLEtBQUssQ0FBQytELFNBQU4sQ0FBZ0J6RSxFQUFoQyxHQUFxQyw4QkFBckMsR0FBc0UsMkJBQTFGLENBQWY7QUFDQSxNQUFNNEUsTUFBTSxHQUFHRCxNQUFNLEdBQUdBLE1BQU0sQ0FBQ2pELEtBQVYsR0FBa0IsRUFBdkM7O0FBRUEsTUFBSyxDQUFFLGFBQWFrRCxNQUFiLElBQXVCLFlBQVlBLE1BQXJDLEtBQWlELENBQUV2RixNQUFNLENBQUNrRixPQUFQLENBQWdCaEYsYUFBaEIsQ0FBeEQsRUFBMEY7QUFDekZtQixTQUFLLENBQUMyRCxjQUFOO0FBQ0EsR0FGRCxNQUVPLElBQUssQ0FBRSxhQUFhTyxNQUFiLElBQXVCLGlCQUFpQkEsTUFBMUMsS0FBc0QsQ0FBRXZGLE1BQU0sQ0FBQ2tGLE9BQVAsQ0FBZ0IvRSxhQUFoQixDQUE3RCxFQUErRjtBQUNyR2tCLFNBQUssQ0FBQzJELGNBQU47QUFDQTtBQUNELENBZkQsRSIsImZpbGUiOiIuL2Fzc2V0cy9qcy9hZG1pbi5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpbiIsIi8vIFRoZSBtb2R1bGUgY2FjaGVcbnZhciBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX18gPSB7fTtcblxuLy8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbmZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG5cdHZhciBjYWNoZWRNb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdO1xuXHRpZiAoY2FjaGVkTW9kdWxlICE9PSB1bmRlZmluZWQpIHtcblx0XHRyZXR1cm4gY2FjaGVkTW9kdWxlLmV4cG9ydHM7XG5cdH1cblx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcblx0dmFyIG1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF0gPSB7XG5cdFx0Ly8gbm8gbW9kdWxlLmlkIG5lZWRlZFxuXHRcdC8vIG5vIG1vZHVsZS5sb2FkZWQgbmVlZGVkXG5cdFx0ZXhwb3J0czoge31cblx0fTtcblxuXHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cblx0X193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0obW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cblx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcblx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xufVxuXG4iLCIvLyBnZXREZWZhdWx0RXhwb3J0IGZ1bmN0aW9uIGZvciBjb21wYXRpYmlsaXR5IHdpdGggbm9uLWhhcm1vbnkgbW9kdWxlc1xuX193ZWJwYWNrX3JlcXVpcmVfXy5uID0gKG1vZHVsZSkgPT4ge1xuXHR2YXIgZ2V0dGVyID0gbW9kdWxlICYmIG1vZHVsZS5fX2VzTW9kdWxlID9cblx0XHQoKSA9PiAobW9kdWxlWydkZWZhdWx0J10pIDpcblx0XHQoKSA9PiAobW9kdWxlKTtcblx0X193ZWJwYWNrX3JlcXVpcmVfXy5kKGdldHRlciwgeyBhOiBnZXR0ZXIgfSk7XG5cdHJldHVybiBnZXR0ZXI7XG59OyIsIi8vIGRlZmluZSBnZXR0ZXIgZnVuY3Rpb25zIGZvciBoYXJtb255IGV4cG9ydHNcbl9fd2VicGFja19yZXF1aXJlX18uZCA9IChleHBvcnRzLCBkZWZpbml0aW9uKSA9PiB7XG5cdGZvcih2YXIga2V5IGluIGRlZmluaXRpb24pIHtcblx0XHRpZihfX3dlYnBhY2tfcmVxdWlyZV9fLm8oZGVmaW5pdGlvbiwga2V5KSAmJiAhX193ZWJwYWNrX3JlcXVpcmVfXy5vKGV4cG9ydHMsIGtleSkpIHtcblx0XHRcdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShleHBvcnRzLCBrZXksIHsgZW51bWVyYWJsZTogdHJ1ZSwgZ2V0OiBkZWZpbml0aW9uW2tleV0gfSk7XG5cdFx0fVxuXHR9XG59OyIsIl9fd2VicGFja19yZXF1aXJlX18ubyA9IChvYmosIHByb3ApID0+IChPYmplY3QucHJvdG90eXBlLmhhc093blByb3BlcnR5LmNhbGwob2JqLCBwcm9wKSkiLCIvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSAoZXhwb3J0cykgPT4ge1xuXHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcblx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcblx0fVxuXHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xufTsiLCIvKiBnbG9iYWwgZGR3Y3dsQWRtaW5PYmosIHRpbnltY2UgKi9cbid1c2Ugc3RyaWN0JztcblxuaW1wb3J0ICcuL2FkbWluLmxlc3MnO1xuXG5jb25zdCB7IGNvbmZpcm1EZWxldGUsIGNvbmZpcm1Ob3RpZnksIGFqYXhVcmwsIHByZXZpZXdOb25jZSwgcHJldmlld1RpdGxlLCBjbG9zZSwgc2VuZGluZywgZXJyb3IgfSA9IHdpbmRvdy5kZHdjd2xBZG1pbk9iaiB8fCB7fTtcblxuLyoqXG4gKiBUb2dnbGUgYW4gaW5saW5lIGFkbWluIGZvcm0gKGFkZCBzdWJzY3JpYmVyKS5cbiAqXG4gKiBAcGFyYW0ge3N0cmluZ30gaWQgRm9ybSBJRC5cbiAqL1xuY29uc3QgdG9nZ2xlRm9ybSA9ICggaWQgKSA9PiB7XG5cdGNvbnN0IGZvcm0gPSBkb2N1bWVudC5nZXRFbGVtZW50QnlJZCggaWQgKTtcblxuXHRpZiAoICEgZm9ybSApIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRmb3JtLmhpZGRlbiA9ICEgZm9ybS5oaWRkZW47XG5cblx0aWYgKCAhIGZvcm0uaGlkZGVuICkge1xuXHRcdGNvbnN0IGlucHV0ID0gZm9ybS5xdWVyeVNlbGVjdG9yKCAnaW5wdXQ6bm90KFt0eXBlPVwiaGlkZGVuXCJdKScgKTtcblxuXHRcdGlmICggaW5wdXQgKSB7XG5cdFx0XHRpbnB1dC5mb2N1cygpO1xuXHRcdH1cblx0fVxufTtcblxuLyoqXG4gKiBJbnNlcnQgYSBwbGFjZWhvbGRlciBpbnRvIHRoZSBsYXN0IGZvY3VzZWQgc3ViamVjdCwgaGVhZGluZyBvciBjb250ZW50IGZpZWxkLlxuICovXG5sZXQgbGFzdEZpZWxkID0gbnVsbDtcblxuZG9jdW1lbnQuYWRkRXZlbnRMaXN0ZW5lciggJ2ZvY3VzaW4nLCAoIGV2ZW50ICkgPT4ge1xuXHRpZiAoIGV2ZW50LnRhcmdldC5tYXRjaGVzKCAnLmRkd2N3bC1wbGFjZWhvbGRlci10YXJnZXQsIC53cC1lZGl0b3ItYXJlYScgKSApIHtcblx0XHRsYXN0RmllbGQgPSBldmVudC50YXJnZXQ7XG5cdH1cbn0gKTtcblxuY29uc3QgaW5zZXJ0UGxhY2Vob2xkZXIgPSAoIHBsYWNlaG9sZGVyICkgPT4ge1xuXHRjb25zdCBlZGl0b3JJZCA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcud3AtZWRpdG9yLWFyZWEnICkgPyBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKCAnLndwLWVkaXRvci1hcmVhJyApLmlkIDogJyc7XG5cdGNvbnN0IGVkaXRvciA9IHdpbmRvdy50aW55bWNlICYmIGVkaXRvcklkID8gdGlueW1jZS5nZXQoIGVkaXRvcklkICkgOiBudWxsO1xuXG5cdC8vIFZpc3VhbCBlZGl0b3IgYWN0aXZlIGFuZCBubyB0ZXh0IGZpZWxkIHBpY2tlZDogaW5zZXJ0IGludG8gdGhlIGNvbnRlbnQuXG5cdGlmICggKCAhIGxhc3RGaWVsZCB8fCBsYXN0RmllbGQuY2xhc3NMaXN0LmNvbnRhaW5zKCAnd3AtZWRpdG9yLWFyZWEnICkgKSAmJiBlZGl0b3IgJiYgISBlZGl0b3IuaXNIaWRkZW4oKSApIHtcblx0XHRlZGl0b3IuZXhlY0NvbW1hbmQoICdtY2VJbnNlcnRDb250ZW50JywgZmFsc2UsIHBsYWNlaG9sZGVyICk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0Y29uc3QgZmllbGQgPSBsYXN0RmllbGQgfHwgZG9jdW1lbnQucXVlcnlTZWxlY3RvciggJy53cC1lZGl0b3ItYXJlYScgKTtcblxuXHRpZiAoICEgZmllbGQgKSB7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0Y29uc3Qgc3RhcnQgPSBmaWVsZC5zZWxlY3Rpb25TdGFydCA/PyBmaWVsZC52YWx1ZS5sZW5ndGg7XG5cdGNvbnN0IGVuZCA9IGZpZWxkLnNlbGVjdGlvbkVuZCA/PyBmaWVsZC52YWx1ZS5sZW5ndGg7XG5cblx0ZmllbGQudmFsdWUgPSBmaWVsZC52YWx1ZS5zbGljZSggMCwgc3RhcnQgKSArIHBsYWNlaG9sZGVyICsgZmllbGQudmFsdWUuc2xpY2UoIGVuZCApO1xuXHRmaWVsZC5mb2N1cygpO1xuXHRmaWVsZC5zZXRTZWxlY3Rpb25SYW5nZSggc3RhcnQgKyBwbGFjZWhvbGRlci5sZW5ndGgsIHN0YXJ0ICsgcGxhY2Vob2xkZXIubGVuZ3RoICk7XG59O1xuXG4vKipcbiAqIE9wZW4gdGhlIGVtYWlsIHByZXZpZXcgaW4gYSBkaWFsb2cuXG4gKlxuICogQHBhcmFtIHtzdHJpbmd9IHVybCBQcmV2aWV3IFVSTC5cbiAqL1xuY29uc3Qgb3BlblByZXZpZXcgPSAoIHVybCApID0+IHtcblx0bGV0IGRpYWxvZyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLXByZXZpZXctZGlhbG9nJyApO1xuXG5cdGlmICggISBkaWFsb2cgKSB7XG5cdFx0ZGlhbG9nID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2RpYWxvZycgKTtcblx0XHRkaWFsb2cuY2xhc3NOYW1lID0gJ2Rkd2N3bC1wcmV2aWV3LWRpYWxvZyc7XG5cdFx0ZGlhbG9nLmlubmVySFRNTCA9ICc8ZGl2IGNsYXNzPVwiZGR3Y3dsLXByZXZpZXctaGVhZFwiPjxzdHJvbmc+PC9zdHJvbmc+PGJ1dHRvbiB0eXBlPVwiYnV0dG9uXCIgY2xhc3M9XCJkZHdjd2wtcHJldmlldy1jbG9zZVwiPjxzcGFuIGNsYXNzPVwiZGFzaGljb25zIGRhc2hpY29ucy1uby1hbHRcIiBhcmlhLWhpZGRlbj1cInRydWVcIj48L3NwYW4+PC9idXR0b24+PC9kaXY+PGlmcmFtZSB0aXRsZT1cIlwiPjwvaWZyYW1lPic7XG5cdFx0ZGlhbG9nLnF1ZXJ5U2VsZWN0b3IoICdzdHJvbmcnICkudGV4dENvbnRlbnQgPSBwcmV2aWV3VGl0bGU7XG5cdFx0ZGlhbG9nLnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLXByZXZpZXctY2xvc2UnICkuc2V0QXR0cmlidXRlKCAnYXJpYS1sYWJlbCcsIGNsb3NlICk7XG5cdFx0ZGlhbG9nLnF1ZXJ5U2VsZWN0b3IoICdpZnJhbWUnICkudGl0bGUgPSBwcmV2aWV3VGl0bGU7XG5cdFx0ZGlhbG9nLnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLXByZXZpZXctY2xvc2UnICkuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCkgPT4gZGlhbG9nLmNsb3NlKCkgKTtcblx0XHRkaWFsb2cuYWRkRXZlbnRMaXN0ZW5lciggJ2NsaWNrJywgKCBldmVudCApID0+IGV2ZW50LnRhcmdldCA9PT0gZGlhbG9nICYmIGRpYWxvZy5jbG9zZSgpICk7XG5cdFx0ZG9jdW1lbnQuYm9keS5hcHBlbmRDaGlsZCggZGlhbG9nICk7XG5cdH1cblxuXHRkaWFsb2cucXVlcnlTZWxlY3RvciggJ2lmcmFtZScgKS5zcmMgPSB1cmw7XG5cdGRpYWxvZy5zaG93TW9kYWwoKTtcbn07XG5cbi8qKlxuICogU2VuZCBhIHRlc3QgZW1haWwuXG4gKlxuICogQHBhcmFtIHtIVE1MRWxlbWVudH0gdG9vbGJhciBUb29sYmFyIGVsZW1lbnQuXG4gKi9cbmNvbnN0IHNlbmRUZXN0ID0gKCB0b29sYmFyICkgPT4ge1xuXHRjb25zdCBidXR0b24gPSB0b29sYmFyLnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLXNlbmQtdGVzdCcgKTtcblx0Y29uc3QgcmVzdWx0ID0gdG9vbGJhci5xdWVyeVNlbGVjdG9yKCAnLmRkd2N3bC10ZXN0LXJlc3VsdCcgKTtcblx0Y29uc3QgaW5wdXQgPSB0b29sYmFyLnF1ZXJ5U2VsZWN0b3IoICdpbnB1dFt0eXBlPVwiZW1haWxcIl0nICk7XG5cblx0aWYgKCAhIGlucHV0LmNoZWNrVmFsaWRpdHkoKSB8fCAhIGlucHV0LnZhbHVlICkge1xuXHRcdGlucHV0LnJlcG9ydFZhbGlkaXR5KCk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0Y29uc3QgZGF0YSA9IG5ldyBGb3JtRGF0YSgpO1xuXG5cdGRhdGEuYXBwZW5kKCAnYWN0aW9uJywgJ2Rkd2N3bF9zZW5kX3Rlc3RfZW1haWwnICk7XG5cdGRhdGEuYXBwZW5kKCAnbm9uY2UnLCBwcmV2aWV3Tm9uY2UgKTtcblx0ZGF0YS5hcHBlbmQoICd0eXBlJywgdG9vbGJhci5kYXRhc2V0LnR5cGUgKTtcblx0ZGF0YS5hcHBlbmQoICdyZWNpcGllbnQnLCBpbnB1dC52YWx1ZSApO1xuXG5cdGJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cdHJlc3VsdC5jbGFzc05hbWUgPSAnZGR3Y3dsLXRlc3QtcmVzdWx0Jztcblx0cmVzdWx0LnRleHRDb250ZW50ID0gc2VuZGluZztcblxuXHRmZXRjaCggYWpheFVybCwgeyBtZXRob2Q6ICdQT1NUJywgY3JlZGVudGlhbHM6ICdzYW1lLW9yaWdpbicsIGJvZHk6IGRhdGEgfSApXG5cdFx0LnRoZW4oICggcmVzcG9uc2UgKSA9PiByZXNwb25zZS5qc29uKCkgKVxuXHRcdC50aGVuKCAoIHsgc3VjY2VzcywgZGF0YTogcmVzcG9uc2UgfSApID0+IHtcblx0XHRcdHJlc3VsdC5jbGFzc0xpc3QuYWRkKCBzdWNjZXNzID8gJ2Rkd2N3bC1zdWNjZXNzJyA6ICdkZHdjd2wtZXJyb3InICk7XG5cdFx0XHRyZXN1bHQudGV4dENvbnRlbnQgPSByZXNwb25zZS5tZXNzYWdlIHx8IGVycm9yO1xuXHRcdH0gKVxuXHRcdC5jYXRjaCggKCkgPT4ge1xuXHRcdFx0cmVzdWx0LmNsYXNzTGlzdC5hZGQoICdkZHdjd2wtZXJyb3InICk7XG5cdFx0XHRyZXN1bHQudGV4dENvbnRlbnQgPSBlcnJvcjtcblx0XHR9IClcblx0XHQuZmluYWxseSggKCkgPT4gKCBidXR0b24uZGlzYWJsZWQgPSBmYWxzZSApICk7XG59O1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnY2xpY2snLCAoIGV2ZW50ICkgPT4ge1xuXHRjb25zdCB0YXJnZXQgPSBldmVudC50YXJnZXQ7XG5cblx0aWYgKCB0YXJnZXQuY2xvc2VzdCggJy5kZHdjd2wtdG9nZ2xlLWFkZCcgKSApIHtcblx0XHRldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuXHRcdHRvZ2dsZUZvcm0oICdkZHdjd2wtYWRkLXN1YnNjcmliZXInICk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0aWYgKCB0YXJnZXQuY2xvc2VzdCggJy5kZHdjd2wtcGxhY2Vob2xkZXInICkgKSB7XG5cdFx0ZXZlbnQucHJldmVudERlZmF1bHQoKTtcblx0XHRpbnNlcnRQbGFjZWhvbGRlciggdGFyZ2V0LmNsb3Nlc3QoICcuZGR3Y3dsLXBsYWNlaG9sZGVyJyApLmRhdGFzZXQucGxhY2Vob2xkZXIgKTtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRpZiAoIHRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1wcmV2aWV3LWVtYWlsJyApICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0b3BlblByZXZpZXcoIHRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1wcmV2aWV3LWVtYWlsJyApLmRhdGFzZXQudXJsICk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0aWYgKCB0YXJnZXQuY2xvc2VzdCggJy5kZHdjd2wtc2VuZC10ZXN0JyApICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0c2VuZFRlc3QoIHRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1lbWFpbC10b29sYmFyJyApICk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0Y29uc3QgbGluayA9IHRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1jb25maXJtLWRlbGV0ZSwgLmRkd2N3bC1jb25maXJtLW5vdGlmeScgKTtcblxuXHRpZiAoIGxpbmsgJiYgISB3aW5kb3cuY29uZmlybSggbGluay5jbGFzc0xpc3QuY29udGFpbnMoICdkZHdjd2wtY29uZmlybS1kZWxldGUnICkgPyBjb25maXJtRGVsZXRlIDogY29uZmlybU5vdGlmeSApICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdH1cbn0gKTtcblxuLy8gRW50ZXIgaW4gdGhlIHRlc3QgZW1haWwgZmllbGQgc2VuZHMgdGhlIHRlc3QgaW5zdGVhZCBvZiBzYXZpbmcgdGhlIHNldHRpbmdzIGZvcm0uXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAna2V5ZG93bicsICggZXZlbnQgKSA9PiB7XG5cdGlmICggJ0VudGVyJyA9PT0gZXZlbnQua2V5ICYmIGV2ZW50LnRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC10ZXN0LWVtYWlsJyApICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0c2VuZFRlc3QoIGV2ZW50LnRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1lbWFpbC10b29sYmFyJyApICk7XG5cdH1cbn0gKTtcblxuLy8gQ29uZmlybSBkZXN0cnVjdGl2ZSBidWxrIGFjdGlvbnMuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnc3VibWl0JywgKCBldmVudCApID0+IHtcblx0Y29uc3QgZm9ybSA9IGV2ZW50LnRhcmdldDtcblxuXHRpZiAoICEgZm9ybS5xdWVyeVNlbGVjdG9yKCAnLndwLWxpc3QtdGFibGUnICkgfHwgISBldmVudC5zdWJtaXR0ZXIgfHwgISAvXmRvYWN0aW9uMj8kLy50ZXN0KCBldmVudC5zdWJtaXR0ZXIuaWQgKSApIHtcblx0XHRyZXR1cm47XG5cdH1cblxuXHRjb25zdCBzZWxlY3QgPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoICdkb2FjdGlvbjInID09PSBldmVudC5zdWJtaXR0ZXIuaWQgPyAnI2J1bGstYWN0aW9uLXNlbGVjdG9yLWJvdHRvbScgOiAnI2J1bGstYWN0aW9uLXNlbGVjdG9yLXRvcCcgKTtcblx0Y29uc3QgYWN0aW9uID0gc2VsZWN0ID8gc2VsZWN0LnZhbHVlIDogJyc7XG5cblx0aWYgKCAoICdkZWxldGUnID09PSBhY3Rpb24gfHwgJ2NsZWFyJyA9PT0gYWN0aW9uICkgJiYgISB3aW5kb3cuY29uZmlybSggY29uZmlybURlbGV0ZSApICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdH0gZWxzZSBpZiAoICggJ25vdGlmeScgPT09IGFjdGlvbiB8fCAnbm90aWZ5X2FsbCcgPT09IGFjdGlvbiApICYmICEgd2luZG93LmNvbmZpcm0oIGNvbmZpcm1Ob3RpZnkgKSApIHtcblx0XHRldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xuXHR9XG59ICk7XG4iXSwic291cmNlUm9vdCI6IiJ9