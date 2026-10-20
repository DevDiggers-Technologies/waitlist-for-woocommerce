/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ 745:
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
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js
function _arrayLikeToArray(arr, len) {
  if (len == null || len > arr.length) len = arr.length;

  for (var i = 0, arr2 = new Array(len); i < len; i++) {
    arr2[i] = arr[i];
  }

  return arr2;
}
;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js

function _arrayWithoutHoles(arr) {
  if (Array.isArray(arr)) return _arrayLikeToArray(arr);
}
;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/iterableToArray.js
function _iterableToArray(iter) {
  if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter);
}
;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js

function _unsupportedIterableToArray(o, minLen) {
  if (!o) return;
  if (typeof o === "string") return _arrayLikeToArray(o, minLen);
  var n = Object.prototype.toString.call(o).slice(8, -1);
  if (n === "Object" && o.constructor) n = o.constructor.name;
  if (n === "Map" || n === "Set") return Array.from(o);
  if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen);
}
;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}
;// CONCATENATED MODULE: ../devdiggers-order-communication-for-woocommerce/node_modules/@babel/runtime/helpers/esm/toConsumableArray.js




function _toConsumableArray(arr) {
  return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread();
}
// EXTERNAL MODULE: ./src/front/front.less
var front = __webpack_require__(745);
;// CONCATENATED MODULE: ./src/front/index.js
/* global ddwcwlFrontObj, jQuery */





var _ref = window.ddwcwlFrontObj || {},
    ajaxUrl = _ref.ajaxUrl,
    nonce = _ref.nonce,
    i18n = _ref.i18n;
/**
 * Show an inline message inside a waiting list box.
 *
 * @param {HTMLElement} box     Waiting list wrapper.
 * @param {string}      message Message, empty to clear.
 * @param {boolean}     success Success state.
 */


var showNotice = function showNotice(box, message, success) {
  var area = box.querySelector('.ddwcwl-notice-area');
  area.hidden = !message;

  if (!message) {
    area.replaceChildren();
    return;
  }

  var notice = document.createElement('div');
  var custom = box.dataset.customNotice;
  notice.setAttribute('role', success ? 'status' : 'alert');
  notice.textContent = message;
  notice.className = 'ddwcwl-message ' + (success ? 'ddwcwl-message-success' : 'ddwcwl-message-error');

  if (custom) {
    notice.className = 'ddwcwl-custom-notice';
    notice.setAttribute('style', custom);
  }

  area.replaceChildren(notice);
};
/**
 * Toggle a fact line and fill its value.
 *
 * @param {HTMLElement} box      Wrapper.
 * @param {string}      selector Element selector.
 * @param {boolean}     visible  Visible.
 */


var toggle = function toggle(box, selector, visible) {
  var el = box.querySelector(selector);

  if (el) {
    el.hidden = !visible;
  }
};
/**
 * Paint a product/variation state onto the box: count, joined, position.
 *
 * @param {HTMLElement} box   Wrapper.
 * @param {Object}      state State from PHP or a join response.
 */


var applyState = function applyState(box, state) {
  var count = Number(state.count) || 0;
  var countLine = box.querySelector('.ddwcwl-count');

  if (countLine) {
    countLine.textContent = (1 === count ? i18n.countSingular : i18n.countPlural).replace('%s', count.toLocaleString());
  }

  toggle(box, '.ddwcwl-fact-count', !!box.dataset.showCount && count > 0);
  var position = box.querySelector('.ddwcwl-position-value');

  if (position) {
    position.textContent = (Number(state.position) || 0).toLocaleString();
  }

  toggle(box, '.ddwcwl-position', !!box.dataset.showPosition && Number(state.position) > 0);
  box.classList.toggle('ddwcwl-joined', !!state.joined);
  var facts = box.querySelector('.ddwcwl-facts');

  if (facts) {
    facts.hidden = !_toConsumableArray(facts.querySelectorAll('.ddwcwl-fact')).some(function (fact) {
      return !fact.hidden && 'none' !== window.getComputedStyle(fact).display;
    });
  }
};
/**
 * Point every product id input of a box at a product or variation.
 *
 * @param {HTMLElement} box Wrapper.
 * @param {number}      id  Product ID.
 */


var setProduct = function setProduct(box, id) {
  box.querySelectorAll('.ddwcwl-product-id').forEach(function (input) {
    return input.value = id;
  });
  var variations = box.dataset.variations ? JSON.parse(box.dataset.variations) : null;

  if (variations && variations[id]) {
    showNotice(box, '');
    applyState(box, variations[id]);
  }
};
/**
 * Submit a join/leave form over AJAX, falling back to a normal post on network failure.
 *
 * @param {HTMLFormElement} form Form.
 */


var submitForm = function submitForm(form) {
  var box = form.closest('.ddwcwl-waiting-list');
  var button = form.querySelector('button[type="submit"]');
  var data = new FormData(form);
  var leave = 'leave' === data.get('ddwcwl_form_action');
  var email = form.querySelector('.ddwcwl-email');

  if (email && !email.checkValidity()) {
    email.reportValidity();
    return;
  }

  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  data.append('action', leave ? 'ddwcwl_leave_waiting_list' : 'ddwcwl_join_waiting_list');
  data.append('nonce', nonce);
  button.disabled = true;
  box.classList.add('ddwcwl-loading');
  fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    body: data
  }).then(function (response) {
    return response.json();
  }).then(function (_ref2) {
    var success = _ref2.success,
        result = _ref2.data;

    if (!success) {
      showNotice(box, result.message || i18n.error, false);
      return;
    }

    var state = {
      count: result.count,
      joined: !leave,
      position: result.position || 0
    }; // Keep the stored state of the current product in sync.

    var id = data.get('ddwcwl_product_id');
    var variations = box.dataset.variations ? JSON.parse(box.dataset.variations) : null;
    var base = variations ? variations[id] : JSON.parse(box.dataset.state || '{}');
    Object.assign(base || {}, state);

    if (variations && variations[id]) {
      box.dataset.variations = JSON.stringify(variations);
    } else if (!variations) {
      box.dataset.state = JSON.stringify(base);
    }

    applyState(box, base || state);

    if (leave) {
      showNotice(box, result.message, true);
      return;
    }

    showNotice(box, '');
    box.querySelector('.ddwcwl-done-title').textContent = result.title || i18n.joined;
    box.querySelector('.ddwcwl-done-message').textContent = !result.position || !box.dataset.showPosition ? result.message : '';

    if (email) {
      email.value = '';
    }

    box.querySelector('.ddwcwl-done').focus({
      preventScroll: true
    });
  }).catch(function () {
    return form.submit();
  }).finally(function () {
    button.disabled = false;
    box.classList.remove('ddwcwl-loading');
  });
};

document.addEventListener('submit', function (event) {
  var form = event.target.closest('.ddwcwl-form');

  if (form && ajaxUrl) {
    event.preventDefault();
    submitForm(form);
  }
}); // Paint the initial state of simple products (hides empty fact lists).

document.querySelectorAll('.ddwcwl-waiting-list[data-state]').forEach(function (box) {
  if (box.dataset.state) {
    applyState(box, JSON.parse(box.dataset.state));
  }
}); // Variable product pages: show the form only for eligible (out of stock) variations.

if (window.jQuery) {
  /**
   * Waiting list box belonging to a variations form.
   *
   * @param {HTMLElement} form Variations form.
   * @return {HTMLElement|null} Box.
   */
  var findBox = function findBox(form) {
    var scope = form.closest('.product') || document;
    return scope.querySelector('.ddwcwl-context-product[data-variations]:not([data-variations=""])');
  };

  jQuery(document.body).on('found_variation', '.variations_form', function (event, variation) {
    var box = findBox(this);

    if (!box) {
      return;
    }

    var state = JSON.parse(box.dataset.variations)[variation.variation_id];
    box.hidden = !state;

    if (state) {
      setProduct(box, variation.variation_id);
    }
  });
  jQuery(document.body).on('reset_data hide_variation', '.variations_form', function () {
    var box = findBox(this);

    if (box) {
      box.hidden = true;
    }
  });
}
})();

var __webpack_export_target__ = this;
for(var i in __webpack_exports__) __webpack_export_target__[i] = __webpack_exports__[i];
if(__webpack_exports__.__esModule) Object.defineProperty(__webpack_export_target__, "__esModule", { value: true });
/******/ })()
;
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uL3NyYy9mcm9udC9mcm9udC5sZXNzP2U2MWQiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ib290c3RyYXAiLCJ3ZWJwYWNrOi8vZGV2ZGlnZ2Vycy13YWl0bGlzdC1mb3Itd29vY29tbWVyY2Uvd2VicGFjay9ydW50aW1lL21ha2UgbmFtZXNwYWNlIG9iamVjdCIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uLi9kZXZkaWdnZXJzLW9yZGVyLWNvbW11bmljYXRpb24tZm9yLXdvb2NvbW1lcmNlL25vZGVfbW9kdWxlcy9AYmFiZWwvcnVudGltZS9oZWxwZXJzL2VzbS9hcnJheUxpa2VUb0FycmF5LmpzIiwid2VicGFjazovL2RldmRpZ2dlcnMtd2FpdGxpc3QtZm9yLXdvb2NvbW1lcmNlLy4uL2RldmRpZ2dlcnMtb3JkZXItY29tbXVuaWNhdGlvbi1mb3Itd29vY29tbWVyY2Uvbm9kZV9tb2R1bGVzL0BiYWJlbC9ydW50aW1lL2hlbHBlcnMvZXNtL2FycmF5V2l0aG91dEhvbGVzLmpzIiwid2VicGFjazovL2RldmRpZ2dlcnMtd2FpdGxpc3QtZm9yLXdvb2NvbW1lcmNlLy4uL2RldmRpZ2dlcnMtb3JkZXItY29tbXVuaWNhdGlvbi1mb3Itd29vY29tbWVyY2Uvbm9kZV9tb2R1bGVzL0BiYWJlbC9ydW50aW1lL2hlbHBlcnMvZXNtL2l0ZXJhYmxlVG9BcnJheS5qcyIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uLi9kZXZkaWdnZXJzLW9yZGVyLWNvbW11bmljYXRpb24tZm9yLXdvb2NvbW1lcmNlL25vZGVfbW9kdWxlcy9AYmFiZWwvcnVudGltZS9oZWxwZXJzL2VzbS91bnN1cHBvcnRlZEl0ZXJhYmxlVG9BcnJheS5qcyIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uLi9kZXZkaWdnZXJzLW9yZGVyLWNvbW11bmljYXRpb24tZm9yLXdvb2NvbW1lcmNlL25vZGVfbW9kdWxlcy9AYmFiZWwvcnVudGltZS9oZWxwZXJzL2VzbS9ub25JdGVyYWJsZVNwcmVhZC5qcyIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uLi9kZXZkaWdnZXJzLW9yZGVyLWNvbW11bmljYXRpb24tZm9yLXdvb2NvbW1lcmNlL25vZGVfbW9kdWxlcy9AYmFiZWwvcnVudGltZS9oZWxwZXJzL2VzbS90b0NvbnN1bWFibGVBcnJheS5qcyIsIndlYnBhY2s6Ly9kZXZkaWdnZXJzLXdhaXRsaXN0LWZvci13b29jb21tZXJjZS8uL3NyYy9mcm9udC9pbmRleC5qcyJdLCJuYW1lcyI6WyJ3aW5kb3ciLCJkZHdjd2xGcm9udE9iaiIsImFqYXhVcmwiLCJub25jZSIsImkxOG4iLCJzaG93Tm90aWNlIiwiYm94IiwibWVzc2FnZSIsInN1Y2Nlc3MiLCJhcmVhIiwicXVlcnlTZWxlY3RvciIsImhpZGRlbiIsInJlcGxhY2VDaGlsZHJlbiIsIm5vdGljZSIsImRvY3VtZW50IiwiY3JlYXRlRWxlbWVudCIsImN1c3RvbSIsImRhdGFzZXQiLCJjdXN0b21Ob3RpY2UiLCJzZXRBdHRyaWJ1dGUiLCJ0ZXh0Q29udGVudCIsImNsYXNzTmFtZSIsInRvZ2dsZSIsInNlbGVjdG9yIiwidmlzaWJsZSIsImVsIiwiYXBwbHlTdGF0ZSIsInN0YXRlIiwiY291bnQiLCJOdW1iZXIiLCJjb3VudExpbmUiLCJjb3VudFNpbmd1bGFyIiwiY291bnRQbHVyYWwiLCJyZXBsYWNlIiwidG9Mb2NhbGVTdHJpbmciLCJzaG93Q291bnQiLCJwb3NpdGlvbiIsInNob3dQb3NpdGlvbiIsImNsYXNzTGlzdCIsImpvaW5lZCIsImZhY3RzIiwicXVlcnlTZWxlY3RvckFsbCIsInNvbWUiLCJmYWN0IiwiZ2V0Q29tcHV0ZWRTdHlsZSIsImRpc3BsYXkiLCJzZXRQcm9kdWN0IiwiaWQiLCJmb3JFYWNoIiwiaW5wdXQiLCJ2YWx1ZSIsInZhcmlhdGlvbnMiLCJKU09OIiwicGFyc2UiLCJzdWJtaXRGb3JtIiwiZm9ybSIsImNsb3Nlc3QiLCJidXR0b24iLCJkYXRhIiwiRm9ybURhdGEiLCJsZWF2ZSIsImdldCIsImVtYWlsIiwiY2hlY2tWYWxpZGl0eSIsInJlcG9ydFZhbGlkaXR5IiwiYXBwZW5kIiwiZGlzYWJsZWQiLCJhZGQiLCJmZXRjaCIsIm1ldGhvZCIsImNyZWRlbnRpYWxzIiwiYm9keSIsInRoZW4iLCJyZXNwb25zZSIsImpzb24iLCJyZXN1bHQiLCJlcnJvciIsImJhc2UiLCJPYmplY3QiLCJhc3NpZ24iLCJzdHJpbmdpZnkiLCJ0aXRsZSIsImZvY3VzIiwicHJldmVudFNjcm9sbCIsImNhdGNoIiwic3VibWl0IiwiZmluYWxseSIsInJlbW92ZSIsImFkZEV2ZW50TGlzdGVuZXIiLCJldmVudCIsInRhcmdldCIsInByZXZlbnREZWZhdWx0IiwialF1ZXJ5IiwiZmluZEJveCIsInNjb3BlIiwib24iLCJ2YXJpYXRpb24iLCJ2YXJpYXRpb25faWQiXSwibWFwcGluZ3MiOiI7Ozs7OztBQUFBLHVDOzs7Ozs7VUNBQTtVQUNBOztVQUVBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBO1VBQ0E7VUFDQTtVQUNBOztVQUVBO1VBQ0E7O1VBRUE7VUFDQTtVQUNBOzs7OztXQ3RCQTtXQUNBO1dBQ0E7V0FDQSxzREFBc0Qsa0JBQWtCO1dBQ3hFO1dBQ0EsK0NBQStDLGNBQWM7V0FDN0QsRTs7Ozs7Ozs7Ozs7O0FDTmU7QUFDZjs7QUFFQSx3Q0FBd0MsU0FBUztBQUNqRDtBQUNBOztBQUVBO0FBQ0EsQzs7QUNScUQ7QUFDdEM7QUFDZixpQ0FBaUMsaUJBQWdCO0FBQ2pELEM7O0FDSGU7QUFDZjtBQUNBLEM7O0FDRnFEO0FBQ3RDO0FBQ2Y7QUFDQSxvQ0FBb0MsaUJBQWdCO0FBQ3BEO0FBQ0E7QUFDQTtB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O0FBRUEsV0FBT2dGLEtBQUssQ0FBQ3BGLGFBQU4sQ0FBcUIsb0VBQXJCLENBQVA7QUFDQSxHQUpEOztBQU1Ba0YsUUFBTSxDQUFFOUUsUUFBUSxDQUFDeUQsSUFBWCxDQUFOLENBQXdCd0IsRUFBeEIsQ0FBNEIsaUJBQTVCLEVBQStDLGtCQUEvQyxFQUFtRSxVQUFXTixLQUFYLEVBQWtCTyxTQUFsQixFQUE4QjtBQUNoRyxRQUFNMUYsR0FBRyxHQUFHdUYsT0FBTyxDQUFFLElBQUYsQ0FBbkI7O0FBRUEsUUFBSyxDQUFFdkYsR0FBUCxFQUFhO0FBQ1o7QUFDQTs7QUFFRCxRQUFNcUIsS0FBSyxHQUFHeUIsSUFBSSxDQUFDQyxLQUFMLENBQVkvQyxHQUFHLENBQUNXLE9BQUosQ0FBWWtDLFVBQXhCLEVBQXNDNkMsU0FBUyxDQUFDQyxZQUFoRCxDQUFkO0FBRUEzRixPQUFHLENBQUNLLE1BQUosR0FBYSxDQUFFZ0IsS0FBZjs7QUFFQSxRQUFLQSxLQUFMLEVBQWE7QUFDWm1CLGdCQUFVLENBQUV4QyxHQUFGLEVBQU8wRixTQUFTLENBQUNDLFlBQWpCLENBQVY7QUFDQTtBQUNELEdBZEQ7QUFnQkFMLFFBQU0sQ0FBRTlFLFFBQVEsQ0FBQ3lELElBQVgsQ0FBTixDQUF3QndCLEVBQXhCLENBQTRCLDJCQUE1QixFQUF5RCxrQkFBekQsRUFBNkUsWUFBWTtBQUN4RixRQUFNekYsR0FBRyxHQUFHdUYsT0FBTyxDQUFFLElBQUYsQ0FBbkI7O0FBRUEsUUFBS3ZGLEdBQUwsRUFBVztBQUNWQSxTQUFHLENBQUNLLE1BQUosR0FBYSxJQUFiO0FBQ0E7QUFDRCxHQU5EO0FBT0EsQyIsImZpbGUiOiIuL2Fzc2V0cy9qcy9mcm9udC5qcyIsInNvdXJjZXNDb250ZW50IjpbIi8vIGV4dHJhY3RlZCBieSBtaW5pLWNzcy1leHRyYWN0LXBsdWdpbiIsIi8vIFRoZSBtb2R1bGUgY2FjaGVcbnZhciBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX18gPSB7fTtcblxuLy8gVGhlIHJlcXVpcmUgZnVuY3Rpb25cbmZ1bmN0aW9uIF9fd2VicGFja19yZXF1aXJlX18obW9kdWxlSWQpIHtcblx0Ly8gQ2hlY2sgaWYgbW9kdWxlIGlzIGluIGNhY2hlXG5cdHZhciBjYWNoZWRNb2R1bGUgPSBfX3dlYnBhY2tfbW9kdWxlX2NhY2hlX19bbW9kdWxlSWRdO1xuXHRpZiAoY2FjaGVkTW9kdWxlICE9PSB1bmRlZmluZWQpIHtcblx0XHRyZXR1cm4gY2FjaGVkTW9kdWxlLmV4cG9ydHM7XG5cdH1cblx0Ly8gQ3JlYXRlIGEgbmV3IG1vZHVsZSAoYW5kIHB1dCBpdCBpbnRvIHRoZSBjYWNoZSlcblx0dmFyIG1vZHVsZSA9IF9fd2VicGFja19tb2R1bGVfY2FjaGVfX1ttb2R1bGVJZF0gPSB7XG5cdFx0Ly8gbm8gbW9kdWxlLmlkIG5lZWRlZFxuXHRcdC8vIG5vIG1vZHVsZS5sb2FkZWQgbmVlZGVkXG5cdFx0ZXhwb3J0czoge31cblx0fTtcblxuXHQvLyBFeGVjdXRlIHRoZSBtb2R1bGUgZnVuY3Rpb25cblx0X193ZWJwYWNrX21vZHVsZXNfX1ttb2R1bGVJZF0obW9kdWxlLCBtb2R1bGUuZXhwb3J0cywgX193ZWJwYWNrX3JlcXVpcmVfXyk7XG5cblx0Ly8gUmV0dXJuIHRoZSBleHBvcnRzIG9mIHRoZSBtb2R1bGVcblx0cmV0dXJuIG1vZHVsZS5leHBvcnRzO1xufVxuXG4iLCIvLyBkZWZpbmUgX19lc01vZHVsZSBvbiBleHBvcnRzXG5fX3dlYnBhY2tfcmVxdWlyZV9fLnIgPSAoZXhwb3J0cykgPT4ge1xuXHRpZih0eXBlb2YgU3ltYm9sICE9PSAndW5kZWZpbmVkJyAmJiBTeW1ib2wudG9TdHJpbmdUYWcpIHtcblx0XHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgU3ltYm9sLnRvU3RyaW5nVGFnLCB7IHZhbHVlOiAnTW9kdWxlJyB9KTtcblx0fVxuXHRPYmplY3QuZGVmaW5lUHJvcGVydHkoZXhwb3J0cywgJ19fZXNNb2R1bGUnLCB7IHZhbHVlOiB0cnVlIH0pO1xufTsiLCJleHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBfYXJyYXlMaWtlVG9BcnJheShhcnIsIGxlbikge1xuICBpZiAobGVuID09IG51bGwgfHwgbGVuID4gYXJyLmxlbmd0aCkgbGVuID0gYXJyLmxlbmd0aDtcblxuICBmb3IgKHZhciBpID0gMCwgYXJyMiA9IG5ldyBBcnJheShsZW4pOyBpIDwgbGVuOyBpKyspIHtcbiAgICBhcnIyW2ldID0gYXJyW2ldO1xuICB9XG5cbiAgcmV0dXJuIGFycjI7XG59IiwiaW1wb3J0IGFycmF5TGlrZVRvQXJyYXkgZnJvbSBcIi4vYXJyYXlMaWtlVG9BcnJheS5qc1wiO1xuZXhwb3J0IGRlZmF1bHQgZnVuY3Rpb24gX2FycmF5V2l0aG91dEhvbGVzKGFycikge1xuICBpZiAoQXJyYXkuaXNBcnJheShhcnIpKSByZXR1cm4gYXJyYXlMaWtlVG9BcnJheShhcnIpO1xufSIsImV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIF9pdGVyYWJsZVRvQXJyYXkoaXRlcikge1xuICBpZiAodHlwZW9mIFN5bWJvbCAhPT0gXCJ1bmRlZmluZWRcIiAmJiBpdGVyW1N5bWJvbC5pdGVyYXRvcl0gIT0gbnVsbCB8fCBpdGVyW1wiQEBpdGVyYXRvclwiXSAhPSBudWxsKSByZXR1cm4gQXJyYXkuZnJvbShpdGVyKTtcbn0iLCJpbXBvcnQgYXJyYXlMaWtlVG9BcnJheSBmcm9tIFwiLi9hcnJheUxpa2VUb0FycmF5LmpzXCI7XG5leHBvcnQgZGVmYXVsdCBmdW5jdGlvbiBfdW5zdXBwb3J0ZWRJdGVyYWJsZVRvQXJyYXkobywgbWluTGVuKSB7XG4gIGlmICghbykgcmV0dXJuO1xuICBpZiAodHlwZW9mIG8gPT09IFwic3RyaW5nXCIpIHJldHVybiBhcnJheUxpa2VUb0FycmF5KG8sIG1pbkxlbik7XG4gIHZhciBuID0gT2JqZWN0LnByb3RvdHlwZS50b1N0cmluZy5jYWxsKG8pLnNsaWNlKDgsIC0xKTtcbiAgaWYgKG4gPT09IFwiT2JqZWN0XCIgJiYgby5jb25zdHJ1Y3RvcikgbiA9IG8uY29uc3RydWN0b3IubmFtZTtcbiAgaWYgKG4gPT09IFwiTWFwXCIgfHwgbiA9PT0gXCJTZXRcIikgcmV0dXJuIEFycmF5LmZyb20obyk7XG4gIGlmIChuID09PSBcIkFyZ3VtZW50c1wiIHx8IC9eKD86VWl8SSludCg/Ojh8MTZ8MzIpKD86Q2xhbXBlZCk/QXJyYXkkLy50ZXN0KG4pKSByZXR1cm4gYXJyYXlMaWtlVG9BcnJheShvLCBtaW5MZW4pO1xufSIsImV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIF9ub25JdGVyYWJsZVNwcmVhZCgpIHtcbiAgdGhyb3cgbmV3IFR5cGVFcnJvcihcIkludmFsaWQgYXR0ZW1wdCB0byBzcHJlYWQgbm9uLWl0ZXJhYmxlIGluc3RhbmNlLlxcbkluIG9yZGVyIHRvIGJlIGl0ZXJhYmxlLCBub24tYXJyYXkgb2JqZWN0cyBtdXN0IGhhdmUgYSBbU3ltYm9sLml0ZXJhdG9yXSgpIG1ldGhvZC5cIik7XG59IiwiaW1wb3J0IGFycmF5V2l0aG91dEhvbGVzIGZyb20gXCIuL2FycmF5V2l0aG91dEhvbGVzLmpzXCI7XG5pbXBvcnQgaXRlcmFibGVUb0FycmF5IGZyb20gXCIuL2l0ZXJhYmxlVG9BcnJheS5qc1wiO1xuaW1wb3J0IHVuc3VwcG9ydGVkSXRlcmFibGVUb0FycmF5IGZyb20gXCIuL3Vuc3VwcG9ydGVkSXRlcmFibGVUb0FycmF5LmpzXCI7XG5pbXBvcnQgbm9uSXRlcmFibGVTcHJlYWQgZnJvbSBcIi4vbm9uSXRlcmFibGVTcHJlYWQuanNcIjtcbmV4cG9ydCBkZWZhdWx0IGZ1bmN0aW9uIF90b0NvbnN1bWFibGVBcnJheShhcnIpIHtcbiAgcmV0dXJuIGFycmF5V2l0aG91dEhvbGVzKGFycikgfHwgaXRlcmFibGVUb0FycmF5KGFycikgfHwgdW5zdXBwb3J0ZWRJdGVyYWJsZVRvQXJyYXkoYXJyKSB8fCBub25JdGVyYWJsZVNwcmVhZCgpO1xufSIsIi8qIGdsb2JhbCBkZHdjd2xGcm9udE9iaiwgalF1ZXJ5ICovXG4ndXNlIHN0cmljdCc7XG5cbmltcG9ydCAnLi9mcm9udC5sZXNzJztcblxuY29uc3QgeyBhamF4VXJsLCBub25jZSwgaTE4biB9ID0gd2luZG93LmRkd2N3bEZyb250T2JqIHx8IHt9O1xuXG4vKipcbiAqIFNob3cgYW4gaW5saW5lIG1lc3NhZ2UgaW5zaWRlIGEgd2FpdGluZyBsaXN0IGJveC5cbiAqXG4gKiBAcGFyYW0ge0hUTUxFbGVtZW50fSBib3ggICAgIFdhaXRpbmcgbGlzdCB3cmFwcGVyLlxuICogQHBhcmFtIHtzdHJpbmd9ICAgICAgbWVzc2FnZSBNZXNzYWdlLCBlbXB0eSB0byBjbGVhci5cbiAqIEBwYXJhbSB7Ym9vbGVhbn0gICAgIHN1Y2Nlc3MgU3VjY2VzcyBzdGF0ZS5cbiAqL1xuY29uc3Qgc2hvd05vdGljZSA9ICggYm94LCBtZXNzYWdlLCBzdWNjZXNzICkgPT4ge1xuXHRjb25zdCBhcmVhID0gYm94LnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLW5vdGljZS1hcmVhJyApO1xuXG5cdGFyZWEuaGlkZGVuID0gISBtZXNzYWdlO1xuXG5cdGlmICggISBtZXNzYWdlICkge1xuXHRcdGFyZWEucmVwbGFjZUNoaWxkcmVuKCk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0Y29uc3Qgbm90aWNlID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2RpdicgKTtcblx0Y29uc3QgY3VzdG9tID0gYm94LmRhdGFzZXQuY3VzdG9tTm90aWNlO1xuXG5cdG5vdGljZS5zZXRBdHRyaWJ1dGUoICdyb2xlJywgc3VjY2VzcyA/ICdzdGF0dXMnIDogJ2FsZXJ0JyApO1xuXHRub3RpY2UudGV4dENvbnRlbnQgPSBtZXNzYWdlO1xuXHRub3RpY2UuY2xhc3NOYW1lID0gJ2Rkd2N3bC1tZXNzYWdlICcgKyAoIHN1Y2Nlc3MgPyAnZGR3Y3dsLW1lc3NhZ2Utc3VjY2VzcycgOiAnZGR3Y3dsLW1lc3NhZ2UtZXJyb3InICk7XG5cblx0aWYgKCBjdXN0b20gKSB7XG5cdFx0bm90aWNlLmNsYXNzTmFtZSA9ICdkZHdjd2wtY3VzdG9tLW5vdGljZSc7XG5cdFx0bm90aWNlLnNldEF0dHJpYnV0ZSggJ3N0eWxlJywgY3VzdG9tICk7XG5cdH1cblxuXHRhcmVhLnJlcGxhY2VDaGlsZHJlbiggbm90aWNlICk7XG59O1xuXG4vKipcbiAqIFRvZ2dsZSBhIGZhY3QgbGluZSBhbmQgZmlsbCBpdHMgdmFsdWUuXG4gKlxuICogQHBhcmFtIHtIVE1MRWxlbWVudH0gYm94ICAgICAgV3JhcHBlci5cbiAqIEBwYXJhbSB7c3RyaW5nfSAgICAgIHNlbGVjdG9yIEVsZW1lbnQgc2VsZWN0b3IuXG4gKiBAcGFyYW0ge2Jvb2xlYW59ICAgICB2aXNpYmxlICBWaXNpYmxlLlxuICovXG5jb25zdCB0b2dnbGUgPSAoIGJveCwgc2VsZWN0b3IsIHZpc2libGUgKSA9PiB7XG5cdGNvbnN0IGVsID0gYm94LnF1ZXJ5U2VsZWN0b3IoIHNlbGVjdG9yICk7XG5cblx0aWYgKCBlbCApIHtcblx0XHRlbC5oaWRkZW4gPSAhIHZpc2libGU7XG5cdH1cbn07XG5cbi8qKlxuICogUGFpbnQgYSBwcm9kdWN0L3ZhcmlhdGlvbiBzdGF0ZSBvbnRvIHRoZSBib3g6IGNvdW50LCBqb2luZWQsIHBvc2l0aW9uLlxuICpcbiAqIEBwYXJhbSB7SFRNTEVsZW1lbnR9IGJveCAgIFdyYXBwZXIuXG4gKiBAcGFyYW0ge09iamVjdH0gICAgICBzdGF0ZSBTdGF0ZSBmcm9tIFBIUCBvciBhIGpvaW4gcmVzcG9uc2UuXG4gKi9cbmNvbnN0IGFwcGx5U3RhdGUgPSAoIGJveCwgc3RhdGUgKSA9PiB7XG5cdGNvbnN0IGNvdW50ID0gTnVtYmVyKCBzdGF0ZS5jb3VudCApIHx8IDA7XG5cdGNvbnN0IGNvdW50TGluZSA9IGJveC5xdWVyeVNlbGVjdG9yKCAnLmRkd2N3bC1jb3VudCcgKTtcblxuXHRpZiAoIGNvdW50TGluZSApIHtcblx0XHRjb3VudExpbmUudGV4dENvbnRlbnQgPSAoIDEgPT09IGNvdW50ID8gaTE4bi5jb3VudFNpbmd1bGFyIDogaTE4bi5jb3VudFBsdXJhbCApLnJlcGxhY2UoICclcycsIGNvdW50LnRvTG9jYWxlU3RyaW5nKCkgKTtcblx0fVxuXG5cdHRvZ2dsZSggYm94LCAnLmRkd2N3bC1mYWN0LWNvdW50JywgISEgYm94LmRhdGFzZXQuc2hvd0NvdW50ICYmIGNvdW50ID4gMCApO1xuXG5cdGNvbnN0IHBvc2l0aW9uID0gYm94LnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLXBvc2l0aW9uLXZhbHVlJyApO1xuXG5cdGlmICggcG9zaXRpb24gKSB7XG5cdFx0cG9zaXRpb24udGV4dENvbnRlbnQgPSAoIE51bWJlciggc3RhdGUucG9zaXRpb24gKSB8fCAwICkudG9Mb2NhbGVTdHJpbmcoKTtcblx0fVxuXG5cdHRvZ2dsZSggYm94LCAnLmRkd2N3bC1wb3NpdGlvbicsICEhIGJveC5kYXRhc2V0LnNob3dQb3NpdGlvbiAmJiBOdW1iZXIoIHN0YXRlLnBvc2l0aW9uICkgPiAwICk7XG5cdGJveC5jbGFzc0xpc3QudG9nZ2xlKCAnZGR3Y3dsLWpvaW5lZCcsICEhIHN0YXRlLmpvaW5lZCApO1xuXG5cdGNvbnN0IGZhY3RzID0gYm94LnF1ZXJ5U2VsZWN0b3IoICcuZGR3Y3dsLWZhY3RzJyApO1xuXG5cdGlmICggZmFjdHMgKSB7XG5cdFx0ZmFjdHMuaGlkZGVuID0gISBbIC4uLmZhY3RzLnF1ZXJ5U2VsZWN0b3JBbGwoICcuZGR3Y3dsLWZhY3QnICkgXS5zb21lKCAoIGZhY3QgKSA9PiAhIGZhY3QuaGlkZGVuICYmICdub25lJyAhPT0gd2luZG93LmdldENvbXB1dGVkU3R5bGUoIGZhY3QgKS5kaXNwbGF5ICk7XG5cdH1cbn07XG5cbi8qKlxuICogUG9pbnQgZXZlcnkgcHJvZHVjdCBpZCBpbnB1dCBvZiBhIGJveCBhdCBhIHByb2R1Y3Qgb3IgdmFyaWF0aW9uLlxuICpcbiAqIEBwYXJhbSB7SFRNTEVsZW1lbnR9IGJveCBXcmFwcGVyLlxuICogQHBhcmFtIHtudW1iZXJ9ICAgICAgaWQgIFByb2R1Y3QgSUQuXG4gKi9cbmNvbnN0IHNldFByb2R1Y3QgPSAoIGJveCwgaWQgKSA9PiB7XG5cdGJveC5xdWVyeVNlbGVjdG9yQWxsKCAnLmRkd2N3bC1wcm9kdWN0LWlkJyApLmZvckVhY2goICggaW5wdXQgKSA9PiAoIGlucHV0LnZhbHVlID0gaWQgKSApO1xuXG5cdGNvbnN0IHZhcmlhdGlvbnMgPSBib3guZGF0YXNldC52YXJpYXRpb25zID8gSlNPTi5wYXJzZSggYm94LmRhdGFzZXQudmFyaWF0aW9ucyApIDogbnVsbDtcblxuXHRpZiAoIHZhcmlhdGlvbnMgJiYgdmFyaWF0aW9uc1sgaWQgXSApIHtcblx0XHRzaG93Tm90aWNlKCBib3gsICcnICk7XG5cdFx0YXBwbHlTdGF0ZSggYm94LCB2YXJpYXRpb25zWyBpZCBdICk7XG5cdH1cbn07XG5cbi8qKlxuICogU3VibWl0IGEgam9pbi9sZWF2ZSBmb3JtIG92ZXIgQUpBWCwgZmFsbGluZyBiYWNrIHRvIGEgbm9ybWFsIHBvc3Qgb24gbmV0d29yayBmYWlsdXJlLlxuICpcbiAqIEBwYXJhbSB7SFRNTEZvcm1FbGVtZW50fSBmb3JtIEZvcm0uXG4gKi9cbmNvbnN0IHN1Ym1pdEZvcm0gPSAoIGZvcm0gKSA9PiB7XG5cdGNvbnN0IGJveCA9IGZvcm0uY2xvc2VzdCggJy5kZHdjd2wtd2FpdGluZy1saXN0JyApO1xuXHRjb25zdCBidXR0b24gPSBmb3JtLnF1ZXJ5U2VsZWN0b3IoICdidXR0b25bdHlwZT1cInN1Ym1pdFwiXScgKTtcblx0Y29uc3QgZGF0YSA9IG5ldyBGb3JtRGF0YSggZm9ybSApO1xuXHRjb25zdCBsZWF2ZSA9ICdsZWF2ZScgPT09IGRhdGEuZ2V0KCAnZGR3Y3dsX2Zvcm1fYWN0aW9uJyApO1xuXHRjb25zdCBlbWFpbCA9IGZvcm0ucXVlcnlTZWxlY3RvciggJy5kZHdjd2wtZW1haWwnICk7XG5cblx0aWYgKCBlbWFpbCAmJiAhIGVtYWlsLmNoZWNrVmFsaWRpdHkoKSApIHtcblx0XHRlbWFpbC5yZXBvcnRWYWxpZGl0eSgpO1xuXHRcdHJldHVybjtcblx0fVxuXG5cdGlmICggISBmb3JtLmNoZWNrVmFsaWRpdHkoKSApIHtcblx0XHRmb3JtLnJlcG9ydFZhbGlkaXR5KCk7XG5cdFx0cmV0dXJuO1xuXHR9XG5cblx0ZGF0YS5hcHBlbmQoICdhY3Rpb24nLCBsZWF2ZSA/ICdkZHdjd2xfbGVhdmVfd2FpdGluZ19saXN0JyA6ICdkZHdjd2xfam9pbl93YWl0aW5nX2xpc3QnICk7XG5cdGRhdGEuYXBwZW5kKCAnbm9uY2UnLCBub25jZSApO1xuXG5cdGJ1dHRvbi5kaXNhYmxlZCA9IHRydWU7XG5cdGJveC5jbGFzc0xpc3QuYWRkKCAnZGR3Y3dsLWxvYWRpbmcnICk7XG5cblx0ZmV0Y2goIGFqYXhVcmwsIHsgbWV0aG9kOiAnUE9TVCcsIGNyZWRlbnRpYWxzOiAnc2FtZS1vcmlnaW4nLCBib2R5OiBkYXRhIH0gKVxuXHRcdC50aGVuKCAoIHJlc3BvbnNlICkgPT4gcmVzcG9uc2UuanNvbigpIClcblx0XHQudGhlbiggKCB7IHN1Y2Nlc3MsIGRhdGE6IHJlc3VsdCB9ICkgPT4ge1xuXHRcdFx0aWYgKCAhIHN1Y2Nlc3MgKSB7XG5cdFx0XHRcdHNob3dOb3RpY2UoIGJveCwgcmVzdWx0Lm1lc3NhZ2UgfHwgaTE4bi5lcnJvciwgZmFsc2UgKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBzdGF0ZSA9IHtcblx0XHRcdFx0Y291bnQ6IHJlc3VsdC5jb3VudCxcblx0XHRcdFx0am9pbmVkOiAhIGxlYXZlLFxuXHRcdFx0XHRwb3NpdGlvbjogcmVzdWx0LnBvc2l0aW9uIHx8IDAsXG5cdFx0XHR9O1xuXG5cdFx0XHQvLyBLZWVwIHRoZSBzdG9yZWQgc3RhdGUgb2YgdGhlIGN1cnJlbnQgcHJvZHVjdCBpbiBzeW5jLlxuXHRcdFx0Y29uc3QgaWQgPSBkYXRhLmdldCggJ2Rkd2N3bF9wcm9kdWN0X2lkJyApO1xuXHRcdFx0Y29uc3QgdmFyaWF0aW9ucyA9IGJveC5kYXRhc2V0LnZhcmlhdGlvbnMgPyBKU09OLnBhcnNlKCBib3guZGF0YXNldC52YXJpYXRpb25zICkgOiBudWxsO1xuXHRcdFx0Y29uc3QgYmFzZSA9IHZhcmlhdGlvbnMgPyB2YXJpYXRpb25zWyBpZCBdIDogSlNPTi5wYXJzZSggYm94LmRhdGFzZXQuc3RhdGUgfHwgJ3t9JyApO1xuXG5cdFx0XHRPYmplY3QuYXNzaWduKCBiYXNlIHx8IHt9LCBzdGF0ZSApO1xuXG5cdFx0XHRpZiAoIHZhcmlhdGlvbnMgJiYgdmFyaWF0aW9uc1sgaWQgXSApIHtcblx0XHRcdFx0Ym94LmRhdGFzZXQudmFyaWF0aW9ucyA9IEpTT04uc3RyaW5naWZ5KCB2YXJpYXRpb25zICk7XG5cdFx0XHR9IGVsc2UgaWYgKCAhIHZhcmlhdGlvbnMgKSB7XG5cdFx0XHRcdGJveC5kYXRhc2V0LnN0YXRlID0gSlNPTi5zdHJpbmdpZnkoIGJhc2UgKTtcblx0XHRcdH1cblxuXHRcdFx0YXBwbHlTdGF0ZSggYm94LCBiYXNlIHx8IHN0YXRlICk7XG5cblx0XHRcdGlmICggbGVhdmUgKSB7XG5cdFx0XHRcdHNob3dOb3RpY2UoIGJveCwgcmVzdWx0Lm1lc3NhZ2UsIHRydWUgKTtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRzaG93Tm90aWNlKCBib3gsICcnICk7XG5cdFx0XHRib3gucXVlcnlTZWxlY3RvciggJy5kZHdjd2wtZG9uZS10aXRsZScgKS50ZXh0Q29udGVudCA9IHJlc3VsdC50aXRsZSB8fCBpMThuLmpvaW5lZDtcblx0XHRcdGJveC5xdWVyeVNlbGVjdG9yKCAnLmRkd2N3bC1kb25lLW1lc3NhZ2UnICkudGV4dENvbnRlbnQgPSAhIHJlc3VsdC5wb3NpdGlvbiB8fCAhIGJveC5kYXRhc2V0LnNob3dQb3NpdGlvbiA/IHJlc3VsdC5tZXNzYWdlIDogJyc7XG5cblx0XHRcdGlmICggZW1haWwgKSB7XG5cdFx0XHRcdGVtYWlsLnZhbHVlID0gJyc7XG5cdFx0XHR9XG5cblx0XHRcdGJveC5xdWVyeVNlbGVjdG9yKCAnLmRkd2N3bC1kb25lJyApLmZvY3VzKCB7IHByZXZlbnRTY3JvbGw6IHRydWUgfSApO1xuXHRcdH0gKVxuXHRcdC5jYXRjaCggKCkgPT4gZm9ybS5zdWJtaXQoKSApXG5cdFx0LmZpbmFsbHkoICgpID0+IHtcblx0XHRcdGJ1dHRvbi5kaXNhYmxlZCA9IGZhbHNlO1xuXHRcdFx0Ym94LmNsYXNzTGlzdC5yZW1vdmUoICdkZHdjd2wtbG9hZGluZycgKTtcblx0XHR9ICk7XG59O1xuXG5kb2N1bWVudC5hZGRFdmVudExpc3RlbmVyKCAnc3VibWl0JywgKCBldmVudCApID0+IHtcblx0Y29uc3QgZm9ybSA9IGV2ZW50LnRhcmdldC5jbG9zZXN0KCAnLmRkd2N3bC1mb3JtJyApO1xuXG5cdGlmICggZm9ybSAmJiBhamF4VXJsICkge1xuXHRcdGV2ZW50LnByZXZlbnREZWZhdWx0KCk7XG5cdFx0c3VibWl0Rm9ybSggZm9ybSApO1xuXHR9XG59ICk7XG5cbi8vIFBhaW50IHRoZSBpbml0aWFsIHN0YXRlIG9mIHNpbXBsZSBwcm9kdWN0cyAoaGlkZXMgZW1wdHkgZmFjdCBsaXN0cykuXG5kb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCAnLmRkd2N3bC13YWl0aW5nLWxpc3RbZGF0YS1zdGF0ZV0nICkuZm9yRWFjaCggKCBib3ggKSA9PiB7XG5cdGlmICggYm94LmRhdGFzZXQuc3RhdGUgKSB7XG5cdFx0YXBwbHlTdGF0ZSggYm94LCBKU09OLnBhcnNlKCBib3guZGF0YXNldC5zdGF0ZSApICk7XG5cdH1cbn0gKTtcblxuLy8gVmFyaWFibGUgcHJvZHVjdCBwYWdlczogc2hvdyB0aGUgZm9ybSBvbmx5IGZvciBlbGlnaWJsZSAob3V0IG9mIHN0b2NrKSB2YXJpYXRpb25zLlxuaWYgKCB3aW5kb3cualF1ZXJ5ICkge1xuXHQvKipcblx0ICogV2FpdGluZyBsaXN0IGJveCBiZWxvbmdpbmcgdG8gYSB2YXJpYXRpb25zIGZvcm0uXG5cdCAqXG5cdCAqIEBwYXJhbSB7SFRNTEVsZW1lbnR9IGZvcm0gVmFyaWF0aW9ucyBmb3JtLlxuXHQgKiBAcmV0dXJuIHtIVE1MRWxlbWVudHxudWxsfSBCb3guXG5cdCAqL1xuXHRjb25zdCBmaW5kQm94ID0gKCBmb3JtICkgPT4ge1xuXHRcdGNvbnN0IHNjb3BlID0gZm9ybS5jbG9zZXN0KCAnLnByb2R1Y3QnICkgfHwgZG9jdW1lbnQ7XG5cblx0XHRyZXR1cm4gc2NvcGUucXVlcnlTZWxlY3RvciggJy5kZHdjd2wtY29udGV4dC1wcm9kdWN0W2RhdGEtdmFyaWF0aW9uc106bm90KFtkYXRhLXZhcmlhdGlvbnM9XCJcIl0pJyApO1xuXHR9O1xuXG5cdGpRdWVyeSggZG9jdW1lbnQuYm9keSApLm9uKCAnZm91bmRfdmFyaWF0aW9uJywgJy52YXJpYXRpb25zX2Zvcm0nLCBmdW5jdGlvbiAoIGV2ZW50LCB2YXJpYXRpb24gKSB7XG5cdFx0Y29uc3QgYm94ID0gZmluZEJveCggdGhpcyApO1xuXG5cdFx0aWYgKCAhIGJveCApIHtcblx0XHRcdHJldHVybjtcblx0XHR9XG5cblx0XHRjb25zdCBzdGF0ZSA9IEpTT04ucGFyc2UoIGJveC5kYXRhc2V0LnZhcmlhdGlvbnMgKVsgdmFyaWF0aW9uLnZhcmlhdGlvbl9pZCBdO1xuXG5cdFx0Ym94LmhpZGRlbiA9ICEgc3RhdGU7XG5cblx0XHRpZiAoIHN0YXRlICkge1xuXHRcdFx0c2V0UHJvZHVjdCggYm94LCB2YXJpYXRpb24udmFyaWF0aW9uX2lkICk7XG5cdFx0fVxuXHR9ICk7XG5cblx0alF1ZXJ5KCBkb2N1bWVudC5ib2R5ICkub24oICdyZXNldF9kYXRhIGhpZGVfdmFyaWF0aW9uJywgJy52YXJpYXRpb25zX2Zvcm0nLCBmdW5jdGlvbiAoKSB7XG5cdFx0Y29uc3QgYm94ID0gZmluZEJveCggdGhpcyApO1xuXG5cdFx0aWYgKCBib3ggKSB7XG5cdFx0XHRib3guaGlkZGVuID0gdHJ1ZTtcblx0XHR9XG5cdH0gKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=