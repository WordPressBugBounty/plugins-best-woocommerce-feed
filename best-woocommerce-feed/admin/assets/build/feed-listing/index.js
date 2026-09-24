/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/design-system/components/Badge.jsx"
/*!************************************************!*\
  !*** ./src/design-system/components/Badge.jsx ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Badge)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

function Badge({
  variant = 'default',
  children,
  className = '',
  ...rest
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", {
    className: `wpfm-badge wpfm-badge--${variant} ${className}`,
    ...rest,
    children: children
  });
}

/***/ },

/***/ "./src/design-system/components/Checkbox.jsx"
/*!***************************************************!*\
  !*** ./src/design-system/components/Checkbox.jsx ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Checkbox)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);
/**
 * Plain rounded checkbox — distinct from Toggle (a switch, for on/off
 * settings). First consumer: feed-listing-v2's row/select-all selection
 * column (openspec/changes/feed-listing-ui/design.md Decision 5).
 *
 * `indeterminate` is a DOM-only property (no HTML attribute), so it's
 * applied imperatively via a ref rather than passed straight to the
 * `<input>` — used by a "select all" header checkbox when only some rows
 * on the page are selected.
 *
 * `label` always provides the checkbox's accessible name. `visuallyHiddenLabel`
 * (default true) renders it as screen-reader-only text — feed-listing-v2's
 * select-all/select-row checkboxes need an accessible name but no visible
 * "Select all"/"Select feed" text next to them (design reference has bare
 * checkboxes). Pass `visuallyHiddenLabel={false}` for a future consumer that
 * wants the label shown.
 */


function Checkbox({
  checked,
  indeterminate = false,
  onChange,
  disabled = false,
  label,
  visuallyHiddenLabel = true,
  className = ''
}) {
  const inputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (inputRef.current) {
      inputRef.current.indeterminate = indeterminate;
    }
  }, [indeterminate]);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("label", {
    className: `wpfm-checkbox ${disabled ? 'wpfm-checkbox--disabled' : ''} ${className}`.trim(),
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("input", {
      ref: inputRef,
      type: "checkbox",
      className: "wpfm-checkbox__input",
      checked: checked,
      disabled: disabled,
      onChange: e => onChange?.(e.target.checked)
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
      className: "wpfm-checkbox__box",
      "aria-hidden": "true",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("svg", {
        width: "10",
        height: "8",
        viewBox: "0 0 10 8",
        fill: "none",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("path", {
          d: "M1 4l2.5 2.5L9 1",
          stroke: "currentColor",
          strokeWidth: "1.6",
          strokeLinecap: "round",
          strokeLinejoin: "round"
        })
      })
    }), label && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
      className: visuallyHiddenLabel ? 'screen-reader-text' : 'wpfm-checkbox__label',
      children: label
    })]
  });
}

/***/ },

/***/ "./src/design-system/components/EmptyState.jsx"
/*!*****************************************************!*\
  !*** ./src/design-system/components/EmptyState.jsx ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ EmptyState)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

function EmptyState({
  message,
  cta
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
    className: "wpfm-empty-state",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", {
      children: message
    }), cta && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("p", {
      children: cta
    })]
  });
}

/***/ },

/***/ "./src/design-system/components/Modal.jsx"
/*!************************************************!*\
  !*** ./src/design-system/components/Modal.jsx ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Modal)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);


function Modal({
  open,
  onClose,
  title,
  children,
  className = ''
}) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if (!open) return;
    const onKeyDown = e => {
      if (e.key === 'Escape') onClose?.();
    };
    document.addEventListener('keydown', onKeyDown);
    return () => document.removeEventListener('keydown', onKeyDown);
  }, [open, onClose]);
  if (!open) return null;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("div", {
    className: "wpfm-modal-overlay",
    onClick: onClose,
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
      className: `wpfm-modal ${className}`,
      role: "dialog",
      "aria-modal": "true",
      onClick: e => e.stopPropagation(),
      children: [onClose && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("button", {
        type: "button",
        className: "wpfm-modal__close",
        onClick: onClose,
        "aria-label": "Close",
        children: "\xD7"
      }), title && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("h2", {
        className: "wpfm-modal__title",
        children: title
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("div", {
        className: "wpfm-modal__body",
        children: children
      })]
    })
  });
}

/***/ },

/***/ "./src/design-system/components/Pagination.jsx"
/*!*****************************************************!*\
  !*** ./src/design-system/components/Pagination.jsx ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Pagination)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Simple numeric pagination. First consumer: Traffic-by-Source table
 * (design.md Decision 12/13).
 *
 * @param {Object}   root0
 * @param {number}   root0.page        Current 1-indexed page.
 * @param {number}   root0.perPage
 * @param {number}   root0.total       Total row count across all pages.
 * @param {Function} root0.onPageChange Called with the new page number.
 */
function Pagination({
  page,
  perPage,
  total,
  onPageChange
}) {
  const totalPages = Math.max(1, Math.ceil(total / perPage));
  if (totalPages <= 1) {
    return null;
  }
  const start = (page - 1) * perPage + 1;
  const end = Math.min(page * perPage, total);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
    className: "wpfm-pagination",
    role: "navigation",
    "aria-label": "Pagination",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("span", {
      className: "wpfm-pagination__info",
      children: [start, "\u2013", end, " of ", total]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", {
      className: "wpfm-pagination__btn",
      disabled: page <= 1,
      onClick: () => onPageChange(page - 1),
      "aria-label": "Previous page",
      children: "\u2039"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("span", {
      className: "wpfm-pagination__pages",
      children: [page, " / ", totalPages]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("button", {
      className: "wpfm-pagination__btn",
      disabled: page >= totalPages,
      onClick: () => onPageChange(page + 1),
      "aria-label": "Next page",
      children: "\u203A"
    })]
  });
}

/***/ },

/***/ "./src/design-system/components/SectionError.jsx"
/*!*******************************************************!*\
  !*** ./src/design-system/components/SectionError.jsx ***!
  \*******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ SectionError)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

function SectionError({
  message = 'Failed to load data.'
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
    className: "wpfm-section-error",
    children: message
  });
}

/***/ },

/***/ "./src/design-system/components/Select.jsx"
/*!*************************************************!*\
  !*** ./src/design-system/components/Select.jsx ***!
  \*************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Select)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);


/**
 * Thin wrapper around <select> matching WP admin style. First consumer:
 * Analytics UTM filter and date-range presets (design.md Decision 13).
 *
 * `variant="pill"` renders a compact rounded control with the selected
 * label + chevron overlaying a transparent native <select> (still fully
 * keyboard/screen-reader operable — the real <select> handles all input,
 * the overlay is purely visual) instead of the browser's default control
 * chrome. Used where a mockup calls for that denser look (e.g. the UTM
 * analytics table's filter) — extracted here rather than left as a
 * one-off so any future consumer gets it for free.
 *
 * `variant="pill-menu"` is the same compact pill face, but its open state
 * is a real custom listbox (checkmark next to the selected option) instead
 * of the browser's native dropdown — for a mockup that specifically shows
 * that open-state styling (feed-editor-v2 Products step's AND/OR connector
 * pill, design.md Decision 3b). No native `<select>` underneath for this
 * variant — the button + listbox pattern below is itself the full input,
 * with option click/Enter/Space, Escape-to-close, and click-outside-to-close.
 *
 * @param {Object}                       root0
 * @param {string}                       [root0.id]
 * @param {string}                       [root0.label]     Rendered as a visually-hidden <label> when provided.
 * @param {string}                       root0.value
 * @param {Function}                     root0.onChange
 * @param {Array}                        root0.options     [{value, label}]
 * @param {string}                       [root0.className]
 * @param {'default'|'pill'|'pill-menu'} [root0.variant]
 */

function Select({
  id,
  label,
  value,
  onChange,
  options = [],
  className = '',
  variant = 'default'
}) {
  // Hooks run unconditionally regardless of variant (Rules of Hooks) —
  // only 'pill-menu' actually uses this state, the effect below is a
  // no-op for the other two.
  const [menuOpen, setMenuOpen] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const containerRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useEffect)(() => {
    if ('pill-menu' !== variant || !menuOpen) {
      return;
    }
    function handleOutsideEvent(e) {
      if (containerRef.current && !containerRef.current.contains(e.target)) {
        setMenuOpen(false);
      }
    }
    function handleEscape(e) {
      if ('Escape' === e.key) {
        setMenuOpen(false);
      }
    }
    document.addEventListener('mousedown', handleOutsideEvent);
    document.addEventListener('keydown', handleEscape);
    return () => {
      document.removeEventListener('mousedown', handleOutsideEvent);
      document.removeEventListener('keydown', handleEscape);
    };
  }, [variant, menuOpen]);
  function findSelectedOption(opts, val) {
    for (const o of opts) {
      if (Array.isArray(o.options)) {
        const found = o.options.find(sub => sub.value === val);
        if (found) {
          return found;
        }
      } else if (o.value === val) {
        return o;
      }
    }
    return null;
  }
  const selectEl = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("select", {
    id: id,
    className: "wpfm-select__control",
    value: value,
    onChange: e => onChange(e.target.value),
    children: options.map((opt, idx) => {
      if (Array.isArray(opt.options)) {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("optgroup", {
          label: opt.label,
          children: opt.options.map(subOpt => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("option", {
            value: subOpt.value,
            children: subOpt.label
          }, subOpt.value))
        }, opt.label || idx);
      }
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("option", {
        value: opt.value,
        children: opt.label
      }, opt.value);
    })
  });
  if ('pill-menu' === variant) {
    const selected = findSelectedOption(options, value);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
      ref: containerRef,
      className: `wpfm-select wpfm-select--pill-menu ${className}`.trim(),
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("button", {
        type: "button",
        id: id,
        className: "wpfm-select__pill-face",
        "aria-haspopup": "listbox",
        "aria-expanded": menuOpen,
        "aria-label": label || undefined,
        onClick: () => setMenuOpen(open => !open),
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
          className: "wpfm-select__pill-face-label",
          children: selected?.label ?? ''
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
          className: "wpfm-select__pill-face-chevron",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("svg", {
            width: "10",
            height: "10",
            viewBox: "0 0 12 12",
            fill: "none",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("path", {
              d: "M2 4l4 4 4-4",
              stroke: "currentColor",
              strokeWidth: "1.5",
              strokeLinecap: "round",
              strokeLinejoin: "round"
            })
          })
        })]
      }), menuOpen && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("ul", {
        className: "wpfm-select__menu",
        role: "listbox",
        children: options.map(opt => {
          const isSelected = opt.value === value;
          const selectOption = () => {
            onChange(opt.value);
            setMenuOpen(false);
          };
          return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("li", {
            role: "option",
            tabIndex: 0,
            "aria-selected": isSelected,
            className: `wpfm-select__menu-item${isSelected ? ' wpfm-select__menu-item--selected' : ''}`,
            onClick: selectOption,
            onKeyDown: e => {
              if ('Enter' === e.key || ' ' === e.key) {
                e.preventDefault();
                selectOption();
              }
            },
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
              className: "wpfm-select__menu-check",
              "aria-hidden": "true",
              children: isSelected && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("svg", {
                width: "10",
                height: "8",
                viewBox: "0 0 10 8",
                fill: "none",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("path", {
                  d: "M1 4l2.5 2.5L9 1",
                  stroke: "currentColor",
                  strokeWidth: "1.5",
                  strokeLinecap: "round",
                  strokeLinejoin: "round"
                })
              })
            }), opt.label]
          }, opt.value);
        })
      })]
    });
  }
  if ('pill' === variant) {
    const selectedLabel = findSelectedOption(options, value)?.label ?? '';
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
      className: `wpfm-select wpfm-select--pill ${className}`.trim(),
      children: [label && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("span", {
        className: "screen-reader-text",
        children: label
      }), selectEl, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("span", {
        className: "wpfm-select__pill-face",
        "aria-hidden": "true",
        children: [selectedLabel, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("svg", {
          width: "12",
          height: "12",
          viewBox: "0 0 12 12",
          fill: "none",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("path", {
            d: "M2 4l4 4 4-4",
            stroke: "currentColor",
            strokeWidth: "1.5",
            strokeLinecap: "round",
            strokeLinejoin: "round"
          })
        })]
      })]
    });
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
    className: `wpfm-select ${className}`.trim(),
    children: [label && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("label", {
      className: "wpfm-select__label screen-reader-text",
      htmlFor: id,
      children: label
    }), selectEl]
  });
}

/***/ },

/***/ "./src/design-system/components/Skeleton.jsx"
/*!***************************************************!*\
  !*** ./src/design-system/components/Skeleton.jsx ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   SkeletonText: () => (/* binding */ SkeletonText),
/* harmony export */   "default": () => (/* binding */ Skeleton)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

function Skeleton({
  width,
  height,
  className = ''
}) {
  const style = {};
  if (width) style.width = width;
  if (height) style.height = height;
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
    className: `wpfm-skeleton wpfm-skeleton--full ${className}`,
    style: style
  });
}
function SkeletonText({
  lines = 1,
  className = ''
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
    children: Array.from({
      length: lines
    }).map((_, i) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("div", {
      className: `wpfm-skeleton wpfm-skeleton--text ${className}`
    }, i))
  });
}

/***/ },

/***/ "./src/design-system/components/Table.jsx"
/*!************************************************!*\
  !*** ./src/design-system/components/Table.jsx ***!
  \************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Table)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

function Table({
  columns,
  children,
  className = ''
}) {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("table", {
    className: `wpfm-table ${className}`,
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("thead", {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("tr", {
        children: columns.map(col => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("th", {
          style: col.style,
          children: col.label
        }, col.key ?? col.label))
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("tbody", {
      children: children
    })]
  });
}

/***/ },

/***/ "./src/feed-listing/FeedListing.jsx"
/*!******************************************!*\
  !*** ./src/feed-listing/FeedListing.jsx ***!
  \******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FeedListing)
/* harmony export */ });
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _styles_feed_listing_scss__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./styles/feed-listing.scss */ "./src/feed-listing/styles/feed-listing.scss");
/* harmony import */ var _design_system_components_Table__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../design-system/components/Table */ "./src/design-system/components/Table.jsx");
/* harmony import */ var _design_system_components_Pagination__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../design-system/components/Pagination */ "./src/design-system/components/Pagination.jsx");
/* harmony import */ var _design_system_components_Checkbox__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../design-system/components/Checkbox */ "./src/design-system/components/Checkbox.jsx");
/* harmony import */ var _design_system_components_Modal__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../design-system/components/Modal */ "./src/design-system/components/Modal.jsx");
/* harmony import */ var _design_system_components_EmptyState__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ../design-system/components/EmptyState */ "./src/design-system/components/EmptyState.jsx");
/* harmony import */ var _design_system_components_SectionError__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ../design-system/components/SectionError */ "./src/design-system/components/SectionError.jsx");
/* harmony import */ var _design_system_components_Skeleton__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ../design-system/components/Skeleton */ "./src/design-system/components/Skeleton.jsx");
/* harmony import */ var _components_FilterBar__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./components/FilterBar */ "./src/feed-listing/components/FilterBar.jsx");
/* harmony import */ var _components_FeedTableRow__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./components/FeedTableRow */ "./src/feed-listing/components/FeedTableRow.jsx");
/* harmony import */ var _components_ImportFeedModal__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./components/ImportFeedModal */ "./src/feed-listing/components/ImportFeedModal.jsx");
/* harmony import */ var _compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./compat/feedListingSync */ "./src/feed-listing/compat/feedListingSync.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./utils */ "./src/feed-listing/utils.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__);

















const COLUMNS = [{
  key: 'cb',
  label: '',
  style: {
    width: 32
  }
}, {
  key: 'feed',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Feeds', 'rex-product-feed')
}, {
  key: 'products',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Products', 'rex-product-feed')
}, {
  key: 'updated',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Last updated', 'rex-product-feed')
}, {
  key: 'files',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('View/Download', 'rex-product-feed')
}, {
  key: 'status',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Status', 'rex-product-feed')
}, {
  key: 'actions',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Action', 'rex-product-feed'),
  style: {
    textAlign: 'right'
  }
}];
function buildQueryString(params) {
  const usp = new URLSearchParams();
  Object.entries(params).forEach(([key, value]) => {
    if (undefined !== value && null !== value && '' !== value) {
      usp.set(key, value);
    }
  });
  const qs = usp.toString();
  return qs ? `?${qs}` : '';
}
function FeedListing() {
  const [status, setStatus] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const [channel, setChannel] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const [dateRange, setDateRange] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const [search, setSearch] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)('');
  const [page, setPage] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(1);
  const [result, setResult] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [loading, setLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(true);
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [selected, setSelected] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(() => new Set());
  const [deleteTarget, setDeleteTarget] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(null);
  const [busy, setBusy] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const [importModalOpen, setImportModalOpen] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useState)(false);
  const dates = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useMemo)(() => {
    if (!dateRange) {
      return {
        after: '',
        before: ''
      };
    }
    return (0,_utils__WEBPACK_IMPORTED_MODULE_15__.dateRangeForLastDays)(Number(dateRange));
  }, [dateRange]);
  const fetchFeeds = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useCallback)(() => {
    setLoading(true);
    setError(null);
    const qs = buildQueryString({
      page,
      status,
      channel,
      search,
      date_after: dates.after,
      date_before: dates.before
    });
    _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
      path: `/wpfm/v1/feed-listing${qs}`
    }).then(res => {
      setResult(res);
      setSelected(new Set());
    }).catch(err => setError(err.message ?? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Failed to load feeds.', 'rex-product-feed'))).finally(() => setLoading(false));
  }, [page, status, channel, search, dates.after, dates.before]);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    fetchFeeds();
  }, [fetchFeeds]);

  // Any filter change resets to page 1 — otherwise a narrower result set
  // could leave `page` pointing past the new last page.
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.useEffect)(() => {
    setPage(1);
  }, [status, channel, search, dates.after, dates.before]);
  const items = result?.items ?? [];
  const toggleSelect = id => {
    setSelected(prev => {
      const next = new Set(prev);
      if (next.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };
  const allSelected = items.length > 0 && items.every(f => selected.has(f.id));
  const someSelected = items.some(f => selected.has(f.id));
  const toggleSelectAll = () => {
    if (allSelected) {
      setSelected(new Set());
    } else {
      setSelected(new Set(items.map(f => f.id)));
    }
  };
  const handleBulkTrash = () => {
    if (0 === selected.size || busy) {
      return;
    }
    setBusy(true);
    (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__.trashFeeds)(Array.from(selected)).finally(() => setBusy(false)).then(fetchFeeds);
  };
  const handleEmptyTrash = () => {
    if (busy) {
      return;
    }
    // eslint-disable-next-line no-alert
    if (!window.confirm((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Permanently delete every feed in Trash? This cannot be undone.', 'rex-product-feed'))) {
      return;
    }
    setBusy(true);
    (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__.emptyTrash)().finally(() => setBusy(false)).then(fetchFeeds);
  };
  const handleEdit = feed => {
    window.location.href = feed.editUrl;
  };
  const handleDuplicate = feed => {
    if (busy) {
      return;
    }
    setBusy(true);
    (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__.duplicateFeed)(feed.id).finally(() => setBusy(false)).then(fetchFeeds).catch(err => setError(err.message ?? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Failed to duplicate feed.', 'rex-product-feed')));
  };
  const handleConfirmDelete = () => {
    if (!deleteTarget || busy) {
      return;
    }
    setBusy(true);
    const request = 'trash' === deleteTarget.postStatus ? (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__.deleteFeed)(deleteTarget.id) : (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_14__.trashFeeds)([deleteTarget.id]);
    request.finally(() => {
      setBusy(false);
      setDeleteTarget(null);
    }).then(fetchFeeds);
  };
  const addNewFeedUrl = window.wpfmFeedListing?.addNewFeedUrl ?? '#';
  const isPro = !!result?.isPro;
  const importXmlNonce = result?.importXmlNonce ?? '';
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
    className: "wpfm-feed-listing",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
      className: "wpfm-feed-listing__header",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("h1", {
          className: "wpfm-feed-listing__title",
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Where do you want to sell?', 'rex-product-feed')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("p", {
          className: "wpfm-feed-listing__subtitle",
          children: result?.summary ? [(0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.sprintf)(/* translators: 1: healthy feed count, 2: total feed count. */
          (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('%1$d of %2$d feeds are healthy.', 'rex-product-feed'), result.summary.healthy, result.summary.total), result.summary.nextRunLabel ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.sprintf)(/* translators: %s: formatted time of day. */
          (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('The next scheduled run is at %s.', 'rex-product-feed'), result.summary.nextRunLabel) : null].filter(Boolean).join(' ') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Loading feeds…', 'rex-product-feed')
        })]
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
        className: "wpfm-feed-listing__header-actions",
        children: [isPro && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("button", {
          type: "button",
          className: "wpfm-feed-listing__import-feed",
          onClick: () => setImportModalOpen(true),
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Import Feed', 'rex-product-feed')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("a", {
          className: "wpfm-feed-listing__add-new",
          href: addNewFeedUrl,
          children: ["+ ", (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Add New Feed', 'rex-product-feed')]
        })]
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_components_ImportFeedModal__WEBPACK_IMPORTED_MODULE_13__["default"], {
      open: importModalOpen,
      onClose: () => setImportModalOpen(false),
      nonce: importXmlNonce,
      onImported: fetchFeeds
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_components_FilterBar__WEBPACK_IMPORTED_MODULE_11__["default"], {
      status: status,
      onStatusChange: setStatus,
      channel: channel,
      onChannelChange: setChannel,
      channelOptions: result?.channels,
      dateRange: dateRange,
      onDateRangeChange: setDateRange,
      search: search,
      onSearchChange: setSearch,
      selectedCount: selected.size,
      onBulkTrash: handleBulkTrash,
      onEmptyTrash: handleEmptyTrash
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
      className: "wpfm-feed-listing__table-card",
      children: [error && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_SectionError__WEBPACK_IMPORTED_MODULE_9__["default"], {
        message: error
      }), !error && loading && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("div", {
        className: "wpfm-feed-listing__loading",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_Skeleton__WEBPACK_IMPORTED_MODULE_10__.SkeletonText, {
          lines: 5
        })
      }), !error && !loading && 0 === items.length && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_EmptyState__WEBPACK_IMPORTED_MODULE_8__["default"], {
        message: search || status || dateRange ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('No feeds match these filters.', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('No product feeds yet.', 'rex-product-feed')
      }), !error && !loading && items.length > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_Table__WEBPACK_IMPORTED_MODULE_4__["default"], {
        columns: [{
          ...COLUMNS[0],
          label: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_Checkbox__WEBPACK_IMPORTED_MODULE_6__["default"], {
            checked: allSelected,
            indeterminate: someSelected && !allSelected,
            onChange: toggleSelectAll,
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Select all', 'rex-product-feed')
          })
        }, ...COLUMNS.slice(1)],
        children: items.map(feed => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_components_FeedTableRow__WEBPACK_IMPORTED_MODULE_12__["default"], {
          feed: feed,
          selected: selected.has(feed.id),
          onToggleSelect: toggleSelect,
          onEdit: handleEdit,
          onDuplicate: handleDuplicate,
          onDeleteRequest: setDeleteTarget
        }, feed.id))
      })]
    }), result && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)(_design_system_components_Pagination__WEBPACK_IMPORTED_MODULE_5__["default"], {
      page: result.page,
      perPage: result.perPage,
      total: result.total,
      onPageChange: setPage
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)(_design_system_components_Modal__WEBPACK_IMPORTED_MODULE_7__["default"], {
      open: Boolean(deleteTarget),
      onClose: () => setDeleteTarget(null),
      title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Delete feed?', 'rex-product-feed'),
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("p", {
        children: deleteTarget && 'trash' === deleteTarget.postStatus ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('This feed is already in Trash. Deleting it now is permanent and cannot be undone.', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('This feed will be moved to Trash.', 'rex-product-feed')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsxs)("div", {
        className: "wpfm-feed-listing__modal-actions",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("button", {
          type: "button",
          className: "wpfm-feed-listing__modal-cancel",
          onClick: () => setDeleteTarget(null),
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Cancel', 'rex-product-feed')
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_16__.jsx)("button", {
          type: "button",
          className: "wpfm-feed-listing__modal-confirm",
          disabled: busy,
          onClick: handleConfirmDelete,
          children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_2__.__)('Delete', 'rex-product-feed')
        })]
      })]
    })]
  });
}

/***/ },

/***/ "./src/feed-listing/compat/feedListingSync.js"
/*!****************************************************!*\
  !*** ./src/feed-listing/compat/feedListingSync.js ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   deleteFeed: () => (/* binding */ deleteFeed),
/* harmony export */   duplicateFeed: () => (/* binding */ duplicateFeed),
/* harmony export */   emptyTrash: () => (/* binding */ emptyTrash),
/* harmony export */   importXmlFeed: () => (/* binding */ importXmlFeed),
/* harmony export */   trashFeeds: () => (/* binding */ trashFeeds)
/* harmony export */ });
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wpAjaxHelper__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./wpAjaxHelper */ "./src/feed-listing/compat/wpAjaxHelper.js");



/**
 * @param {Array<number|string>} ids
 */
function trashFeeds(ids) {
  return (0,_wpAjaxHelper__WEBPACK_IMPORTED_MODULE_1__.wpAjaxHelperRequest)('wpfm-feed-listing-trash', {
    ids: ids.join(',')
  });
}

/**
 * @param {number|string} id
 */
function deleteFeed(id) {
  return (0,_wpAjaxHelper__WEBPACK_IMPORTED_MODULE_1__.wpAjaxHelperRequest)('wpfm-feed-listing-delete', {
    id
  });
}
function emptyTrash() {
  return (0,_wpAjaxHelper__WEBPACK_IMPORTED_MODULE_1__.wpAjaxHelperRequest)('wpfm-feed-listing-empty-trash', {});
}

/**
 * @param {number|string} id
 */
function duplicateFeed(id) {
  return _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default()({
    path: `/wpfm/v1/feed-listing/${id}/duplicate`,
    method: 'POST'
  });
}

/**
 * Uploads an XML feed export to Pro's existing, unmodified
 * `rex_feed_save_import_xml_feed` admin-ajax handler
 * (best-woocommerce-feed-pro/admin/class-rex-product-feed-pro-ajax.php:248).
 * That handler expects a raw WordPress admin-ajax file upload
 * (`$_FILES['rex_feed_import_feed']` + `check_ajax_referer('rex-wpfm-pro-ajax',
 * 'security')`), not `wpAjaxHelperRequest()`'s JSON-payload contract, so
 * this posts `FormData` directly rather than going through that helper.
 *
 * `nonce` comes from `GET /wpfm/v1/feed-listing`'s `importXmlNonce` (created
 * by the Free plugin's own REST endpoint for this exact action string,
 * `rex-wpfm-pro-ajax`) rather than from Pro's own localized script data,
 * which isn't guaranteed to be enqueued on this v2 page.
 *
 * Pro's handler always calls `wp_send_json_success()`, even for an
 * unrecognized file (it sets `message: 'Invalid file!'` instead of using
 * `wp_send_json_error()`) — callers must check `response.data.message`,
 * not just `response.success`, to detect that case.
 *
 * @param {File}   file
 * @param {string} nonce
 */
function importXmlFeed(file, nonce) {
  const ajaxUrl = typeof window !== 'undefined' && window.wpfmFeedListing?.ajaxUrl || typeof window !== 'undefined' && window.ajaxurl || '/wp-admin/admin-ajax.php';
  const body = new FormData();
  body.set('action', 'rex_feed_save_import_xml_feed');
  body.set('security', nonce);
  body.set('rex_feed_import_feed', file);
  return fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    body
  }).then(response => response.json());
}

/***/ },

/***/ "./src/feed-listing/compat/wpAjaxHelper.js"
/*!*************************************************!*\
  !*** ./src/feed-listing/compat/wpAjaxHelper.js ***!
  \*************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   wpAjaxHelperRequest: () => (/* binding */ wpAjaxHelperRequest)
/* harmony export */ });
/**
 * Low-level client for the bundled `philipnewcomer/wp-ajax-helper` package's
 * admin-ajax.php contract — same wire format as
 * src/feed-editor/compat/wpAjaxHelper.js (this app's own copy, matching the
 * per-app-compat-module convention already established there rather than a
 * cross-app import):
 * - POSTs to `window.wpAjaxHelper.ajaxUrl` (localized globally by the
 *   bundled package once any handle is registered).
 * - `action` is the dash-named action with dashes replaced by underscores.
 * - `nonce` is looked up per-action from `window.wpAjaxHelper.handles`.
 * - The payload goes under `payload[...]`, matching how the PHP callback
 *   receives it as a single `$payload` array.
 *
 * @param {string}            action  Dash-named admin-ajax handle.
 * @param {Record<string, *>} payload Flat map sent as `payload[key]=value`.
 */
function wpAjaxHelperRequest(action, payload) {
  const helper = typeof window !== 'undefined' ? window.wpAjaxHelper : undefined;
  if (!helper || !helper.ajaxUrl) {
    return Promise.reject(new Error('wpAjaxHelper is not available on this page.'));
  }
  const normalizedAction = action.replace(/-/g, '_');
  const nonce = helper.handles?.[normalizedAction] ?? '';
  const body = new URLSearchParams();
  body.set('action', normalizedAction);
  body.set('nonce', nonce);
  Object.entries(payload ?? {}).forEach(([key, value]) => {
    body.set(`payload[${key}]`, value ?? '');
  });
  return fetch(helper.ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    body
  }).then(response => response.json());
}

/***/ },

/***/ "./src/feed-listing/components/ChannelIcon.jsx"
/*!*****************************************************!*\
  !*** ./src/feed-listing/components/ChannelIcon.jsx ***!
  \*****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ChannelIcon)
/* harmony export */ });
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

/**
 * Merchant channel icon — brand image when one is bundled, else a colored
 * initial letter. Same file set, same ICON_FILES/PALETTE approach as
 * src/feed-editor/components/MerchantIcon.jsx and
 * src/analytics/features/traffic-by-source/ChannelIcon.jsx (this codebase's
 * established per-app-copy convention for this exact component — see
 * openspec/changes/feed-listing-ui/tasks.md 4.3). Only 17 of 180+ supported
 * merchants have a bundled asset, so the fallback below is the majority
 * case, not an edge case.
 */
const ICON_FILES = {
  google: 'google.webp',
  facebook: 'facebook.webp',
  meta: 'facebook.webp',
  instagram: 'instagram.webp',
  pinterest: 'pinterest.webp',
  tiktok: 'tiktok.webp',
  bing: 'bing.webp',
  snapchat: 'snapchat.webp',
  twitter: 'x.webp',
  x: 'x.webp',
  vivino: 'vivino.webp',
  custom: 'custom.webp',
  idealo: 'Idealo.svg',
  chatgpt: 'chatgpt.svg',
  chatgpt_ads: 'chatgpt.svg',
  woocommerce: 'woocommerce-logo.webp'
};
const PALETTE = ['#05B6FF', '#2563EB', '#7C3AED', '#DB2777', '#DC2626', '#059669', '#D97706', '#4F46E5', '#BE185D', '#6B21A8'];
function hashString(str) {
  let hash = 0;
  for (let i = 0; i < str.length; i++) {
    hash = (hash << 5) - hash + str.charCodeAt(i);
    hash |= 0;
  }
  return Math.abs(hash);
}
function fallbackColor(name = '') {
  return PALETTE[hashString(name) % PALETTE.length];
}
function matchIconFile(slug) {
  if (!slug || typeof slug !== 'string') {
    return null;
  }
  const normalized = slug.toLowerCase().trim();
  if (normalized.includes('google')) {
    return 'google.webp';
  }
  if (ICON_FILES[normalized]) {
    return ICON_FILES[normalized];
  }
  const key = Object.keys(ICON_FILES).find(k => normalized.includes(k));
  return key ? ICON_FILES[key] : null;
}
function ChannelIcon({
  slug,
  name,
  className = ''
}) {
  const baseUrl = window.wpfmFeedListing?.iconsUrl;
  const file = baseUrl && slug ? matchIconFile(slug) : null;
  const boxClass = `wpfm-channel-icon ${className}`.trim();
  if (file) {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", {
      className: boxClass,
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("img", {
        className: "wpfm-channel-icon__img",
        src: `${baseUrl}${file}`,
        alt: "",
        width: "24",
        height: "24",
        loading: "lazy"
      })
    });
  }
  const initial = (name || slug || '?').trim().charAt(0).toUpperCase() || '?';
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", {
    className: boxClass,
    "aria-hidden": "true",
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("span", {
      className: "wpfm-channel-icon__fallback",
      style: {
        background: fallbackColor(name || slug)
      },
      children: initial
    })
  });
}

/***/ },

/***/ "./src/feed-listing/components/FeedTableRow.jsx"
/*!******************************************************!*\
  !*** ./src/feed-listing/components/FeedTableRow.jsx ***!
  \******************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FeedTableRow)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _design_system_components_Checkbox__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../design-system/components/Checkbox */ "./src/design-system/components/Checkbox.jsx");
/* harmony import */ var _design_system_components_Badge__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../design-system/components/Badge */ "./src/design-system/components/Badge.jsx");
/* harmony import */ var _ChannelIcon__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ChannelIcon */ "./src/feed-listing/components/ChannelIcon.jsx");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../utils */ "./src/feed-listing/utils.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__);







const STATUS_BADGE_VARIANT = {
  ready: 'outline-success',
  warning: 'outline-warning',
  warnings: 'outline-warning',
  processing: 'outline-info',
  failed: 'outline-error',
  draft: 'outline-neutral',
  trash: 'outline-neutral'
};
function ViewIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    width: "14",
    height: "12",
    viewBox: "0 0 14 12",
    fill: "none",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M9.10311 5.925C9.10311 7.1625 8.10311 8.1625 6.86561 8.1625C5.62811 8.1625 4.62811 7.1625 4.62811 5.925C4.62811 4.6875 5.62811 3.6875 6.86561 3.6875C8.10311 3.6875 9.10311 4.6875 9.10311 5.925Z",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M6.86563 11.0937C9.07188 11.0937 11.1281 9.79375 12.5594 7.54375C13.1219 6.6625 13.1219 5.18125 12.5594 4.3C11.1281 2.05 9.07188 0.75 6.86563 0.75C4.65938 0.75 2.60313 2.05 1.17188 4.3C0.609375 5.18125 0.609375 6.6625 1.17188 7.54375C2.60313 9.79375 4.65938 11.0937 6.86563 11.0937Z",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })]
  });
}
function DownloadIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    width: "13",
    height: "13",
    viewBox: "0 0 13 13",
    fill: "none",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M6.51757 10.2977C6.76656 10.2981 7.01317 10.2493 7.24324 10.1541C7.47331 10.0589 7.6823 9.91922 7.85821 9.74301L9.98101 7.62021L8.83213 6.47133L7.3252 7.97825L7.3122 0H5.68719L5.70019 7.96742L4.20302 6.47133L3.05414 7.62021L5.17694 9.74301C5.35281 9.91927 5.5618 10.059 5.79188 10.1542C6.02195 10.2494 6.26858 10.2982 6.51757 10.2977Z",
      fill: "currentColor"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M11.3751 8.6666V11.3749H1.62501V8.6666H0V11.3749C0 11.8059 0.171206 12.2192 0.475954 12.524C0.780702 12.8287 1.19403 12.9999 1.62501 12.9999H11.3751C11.806 12.9999 12.2194 12.8287 12.5241 12.524C12.8289 12.2192 13.0001 11.8059 13.0001 11.3749V8.6666H11.3751Z",
      fill: "currentColor"
    })]
  });
}
function EditIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("svg", {
    width: "15",
    height: "15",
    viewBox: "0 0 17 17",
    fill: "none",
    "aria-hidden": "true",
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M16.0105 5.32085C16.4127 4.9186 16.6341 4.38438 16.6341 3.81612C16.6341 3.24785 16.4127 2.71364 16.0105 2.31138L14.3227 0.623604C13.9204 0.221348 13.3862 0 12.8179 0C12.2497 0 11.7155 0.221348 11.3143 0.62254L0 11.9017V16.6H4.69619L16.0105 5.32085ZM12.8179 2.12834L14.5068 3.81505L12.8147 5.5007L11.127 3.81399L12.8179 2.12834ZM2.12834 14.4717V12.7849L9.6201 5.3166L11.3079 7.00437L3.81718 14.4717H2.12834Z",
      fill: "currentColor"
    })
  });
}
function DuplicateIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    width: "15",
    height: "15",
    viewBox: "0 0 16 16",
    fill: "none",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M13.3333 5.33333H6.66667C5.93029 5.33333 5.33333 5.93029 5.33333 6.66667V13.3333C5.33333 14.0697 5.93029 14.6667 6.66667 14.6667H13.3333C14.0697 14.6667 14.6667 14.0697 14.6667 13.3333V6.66667C14.6667 5.93029 14.0697 5.33333 13.3333 5.33333Z",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M3.33333 10.6667H2.66667C2.29662 10.6667 1.94173 10.5197 1.67918 10.2572C1.41663 9.99464 1.26967 9.63975 1.26967 9.2697V2.66667C1.26967 2.29662 1.41663 1.94173 1.67918 1.67918C1.94173 1.41663 2.29662 1.26967 2.66667 1.26967H9.2697C9.63975 1.26967 9.99464 1.41663 10.2572 1.67918C10.5197 1.94173 10.6667 2.29662 10.6667 2.66667V3.33333",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })]
  });
}
function DeleteIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    width: "15",
    height: "16",
    viewBox: "0 0 16 18",
    fill: "none",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M1.33337 4.83335H14.6667",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M6.33329 8.16669V13.1667",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M9.66667 8.16669V13.1667",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M2.16663 4.83331L2.99996 14.8333C2.99996 15.7538 3.74615 16.5 4.66663 16.5H11.3333C12.2538 16.5 13 15.7538 13 14.8333L13.8333 4.83331",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M5.5 4.83333V2.33333C5.5 1.8731 5.8731 1.5 6.33333 1.5H9.66667C10.1269 1.5 10.5 1.8731 10.5 2.33333V4.83333",
      stroke: "currentColor",
      strokeWidth: "1.5",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })]
  });
}
function ReadyStatusIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("svg", {
    width: "12",
    height: "9",
    viewBox: "0 0 12 9",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    "aria-hidden": "true",
    children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M11 1L4.125 8L1 4.81818",
      stroke: "#04A00F",
      strokeWidth: "2",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    })
  });
}
function WarningStatusIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    width: "15",
    height: "15",
    viewBox: "0 0 15 15",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M7.36847 5.1579V8.84211",
      stroke: "#FFB20B",
      strokeLinecap: "round",
      strokeLinejoin: "round"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("circle", {
      cx: "7.36842",
      cy: "11.0527",
      r: "0.368421",
      fill: "#FFB20B",
      stroke: "#FFB20B"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M11.4241 14H3.31273C1.80771 14 0.841932 12.4003 1.54289 11.0685L5.59858 3.36269C6.34841 1.93801 8.38843 1.93801 9.13826 3.36269L13.194 11.0685C13.8949 12.4003 12.9291 14 11.4241 14Z",
      stroke: "#FFB20B",
      strokeLinecap: "round"
    })]
  });
}
function ProcessingStatusIcon() {
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
    className: "wpfm-feed-listing__status-spinner",
    width: "13",
    height: "13",
    viewBox: "0 0 16 16",
    fill: "none",
    xmlns: "http://www.w3.org/2000/svg",
    "aria-hidden": "true",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("circle", {
      cx: "8",
      cy: "8",
      r: "6.5",
      stroke: "currentColor",
      strokeWidth: "2",
      opacity: "0.25"
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
      d: "M14.5 8A6.5 6.5 0 0 0 8 1.5",
      stroke: "currentColor",
      strokeWidth: "2",
      strokeLinecap: "round"
    })]
  });
}
function StatusBadge({
  status
}) {
  const variant = STATUS_BADGE_VARIANT[status.key] ?? 'outline-neutral';
  let icon = null;
  if ('ready' === status.key) {
    icon = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(ReadyStatusIcon, {});
  } else if ('warning' === status.key || 'warnings' === status.key) {
    icon = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(WarningStatusIcon, {});
  } else if ('processing' === status.key) {
    icon = /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(ProcessingStatusIcon, {});
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)(_design_system_components_Badge__WEBPACK_IMPORTED_MODULE_3__["default"], {
    variant: variant,
    className: "wpfm-feed-listing__status-badge",
    title: status.error_message || undefined,
    children: [icon, /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
      children: status.label
    })]
  });
}
function FeedTableRow({
  feed,
  selected,
  onToggleSelect,
  onEdit,
  onDuplicate,
  onDeleteRequest
}) {
  const [isDuplicating, setIsDuplicating] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const isProcessing = 'processing' === feed.status?.key;
  const scheduleLabel = !feed.schedule?.key || 'no' === feed.schedule?.key || 'No' === feed.schedule?.label ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('No interval', 'rex-product-feed') : feed.schedule?.label;
  const metaParts = [feed.format ? feed.format.toUpperCase() : null, scheduleLabel].filter(Boolean);
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("tr", {
    className: "wpfm-feed-listing__row",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_design_system_components_Checkbox__WEBPACK_IMPORTED_MODULE_2__["default"], {
        checked: selected,
        onChange: () => onToggleSelect(feed.id),
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Select feed', 'rex-product-feed')
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "wpfm-feed-listing__identity",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(_ChannelIcon__WEBPACK_IMPORTED_MODULE_4__["default"], {
          slug: feed.merchant,
          name: feed.merchantLabel
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
            className: "wpfm-feed-listing__row-title",
            href: feed.editUrl,
            children: feed.title
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
            className: "wpfm-feed-listing__meta",
            children: metaParts.map(part => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: part
            }, part))
          })]
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      className: "wpfm-feed-listing__products",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "wpfm-feed-listing__products-wrap",
        tabIndex: "0",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("span", {
          className: "wpfm-feed-listing__products-count",
          children: [feed.productsTotal.toLocaleString(), feed.productsOfTotal > feed.productsTotal && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("span", {
            className: "wpfm-feed-listing__products-of",
            children: [' / ', feed.productsOfTotal.toLocaleString()]
          })]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
          className: "wpfm-feed-listing__products-info-icon",
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('View product breakdown', 'rex-product-feed'),
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("svg", {
            width: "14",
            height: "14",
            viewBox: "0 0 16 16",
            fill: "none",
            xmlns: "http://www.w3.org/2000/svg",
            "aria-hidden": "true",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("circle", {
              cx: "8",
              cy: "8",
              r: "7",
              stroke: "currentColor",
              strokeWidth: "1.5"
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("path", {
              d: "M8 7v4.5M8 4.5h.01",
              stroke: "currentColor",
              strokeWidth: "1.5",
              strokeLinecap: "round"
            })]
          })
        }), feed.productsBreakdown && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
          className: "wpfm-feed-listing__products-popover",
          role: "tooltip",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("div", {
            className: "wpfm-feed-listing__products-popover-title",
            children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Product Breakdown', 'rex-product-feed')
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Total products:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("strong", {
              children: [feed.productsTotal.toLocaleString(), feed.productsOfTotal > feed.productsTotal ? ` / ${feed.productsOfTotal.toLocaleString()}` : '']
            })]
          }), feed.productsBreakdown.total_reviews !== undefined && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Total reviews:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
              children: feed.productsBreakdown.total_reviews.toLocaleString()
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Simple products:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
              children: feed.productsBreakdown.simple.toLocaleString()
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Variable parent:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
              children: feed.productsBreakdown.variable_parent.toLocaleString()
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Variations:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
              children: feed.productsBreakdown.variable.toLocaleString()
            })]
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
            className: "wpfm-feed-listing__products-popover-row",
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("span", {
              children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Group products:', 'rex-product-feed')
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("strong", {
              children: feed.productsBreakdown.group.toLocaleString()
            })]
          })]
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      className: "wpfm-feed-listing__updated",
      title: feed.lastUpdatedGmt || '',
      children: (0,_utils__WEBPACK_IMPORTED_MODULE_5__.formatRelativeTime)(feed.lastUpdatedGmt)
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      children: !feed.feedLinksHidden && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "wpfm-feed-listing__file-actions",
        children: [!feed.isCsvFeed && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("a", {
          className: `wpfm-feed-listing__file-btn ${isProcessing ? 'is-disabled' : ''}`,
          href: feed.feedUrl,
          target: "_blank",
          rel: "noreferrer",
          "aria-disabled": 'draft' === feed.postStatus || isProcessing,
          onClick: e => {
            if ('draft' === feed.postStatus || isProcessing) {
              e.preventDefault();
            }
          },
          title: isProcessing ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Feed is processing...', 'rex-product-feed') : undefined,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(ViewIcon, {}), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('View', 'rex-product-feed')]
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("a", {
          className: `wpfm-feed-listing__file-btn ${isProcessing ? 'is-disabled' : ''}`,
          href: feed.feedUrl,
          target: "_blank",
          rel: "noreferrer",
          download: true,
          "aria-disabled": 'draft' === feed.postStatus || isProcessing,
          onClick: e => {
            if ('draft' === feed.postStatus || isProcessing) {
              e.preventDefault();
            }
          },
          title: isProcessing ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Feed is processing...', 'rex-product-feed') : undefined,
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(DownloadIcon, {}), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Download', 'rex-product-feed')]
        })]
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(StatusBadge, {
        status: feed.status
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("td", {
      className: "wpfm-feed-listing__row-actions",
      style: {
        textAlign: 'right'
      },
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsxs)("div", {
        className: "wpfm-feed-listing__action-btns",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("a", {
          className: `wpfm-feed-listing__action-btn wpfm-feed-listing__action-btn--edit ${isProcessing ? 'is-disabled' : ''}`,
          href: feed.editUrl,
          "aria-disabled": isProcessing,
          "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Edit feed', 'rex-product-feed'),
          title: isProcessing ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Feed is processing...', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Edit', 'rex-product-feed'),
          onClick: e => {
            if (isProcessing) {
              e.preventDefault();
              return;
            }
            if (onEdit) {
              e.preventDefault();
              onEdit(feed);
            }
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(EditIcon, {})
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("button", {
          type: "button",
          className: "wpfm-feed-listing__action-btn wpfm-feed-listing__action-btn--duplicate",
          "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Duplicate feed', 'rex-product-feed'),
          title: isProcessing ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Feed is processing...', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Duplicate', 'rex-product-feed'),
          disabled: isDuplicating || isProcessing,
          onClick: () => {
            if (onDuplicate && !isDuplicating && !isProcessing) {
              setIsDuplicating(true);
              Promise.resolve(onDuplicate(feed)).finally(() => {
                setIsDuplicating(false);
              });
            }
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(DuplicateIcon, {})
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)("button", {
          type: "button",
          className: "wpfm-feed-listing__action-btn wpfm-feed-listing__action-btn--delete",
          "aria-label": (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Delete feed', 'rex-product-feed'),
          title: isProcessing ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Feed is processing...', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Delete', 'rex-product-feed'),
          disabled: isProcessing,
          onClick: () => {
            if (!isProcessing) {
              onDeleteRequest(feed);
            }
          },
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_6__.jsx)(DeleteIcon, {})
        })]
      })
    })]
  });
}

/***/ },

/***/ "./src/feed-listing/components/FilterBar.jsx"
/*!***************************************************!*\
  !*** ./src/feed-listing/components/FilterBar.jsx ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   STATUS_OPTIONS: () => (/* binding */ STATUS_OPTIONS),
/* harmony export */   "default": () => (/* binding */ FilterBar)
/* harmony export */ });
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _design_system_components_Select__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../../design-system/components/Select */ "./src/design-system/components/Select.jsx");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__);



const STATUS_OPTIONS = [{
  value: '',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('All status', 'rex-product-feed')
}, {
  value: 'draft',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Draft', 'rex-product-feed')
}, {
  value: 'processing',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Processing', 'rex-product-feed')
}, {
  value: 'ready',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Ready', 'rex-product-feed')
}, {
  value: 'warning',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Warning', 'rex-product-feed')
}, {
  value: 'failed',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Failed', 'rex-product-feed')
}, {
  value: 'trash',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Trash', 'rex-product-feed')
}];
const DATE_OPTIONS = [{
  value: '',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('All dates', 'rex-product-feed')
}, {
  value: '7',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Last 7 days', 'rex-product-feed')
}, {
  value: '30',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Last 30 days', 'rex-product-feed')
}, {
  value: '90',
  label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Last 90 days', 'rex-product-feed')
}];
function FilterBar({
  status,
  onStatusChange,
  channel,
  onChannelChange,
  channelOptions,
  dateRange,
  onDateRangeChange,
  search,
  onSearchChange,
  selectedCount,
  onBulkTrash,
  onEmptyTrash
}) {
  const resolvedChannelOptions = channelOptions && channelOptions.length > 0 ? channelOptions : [{
    value: '',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('All channels', 'rex-product-feed')
  }];
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
    className: "wpfm-feed-listing__filter-bar",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "wpfm-feed-listing__filter-bar-left",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_design_system_components_Select__WEBPACK_IMPORTED_MODULE_1__["default"], {
        id: "wpfm-feed-listing-status-filter",
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Status', 'rex-product-feed'),
        variant: "pill",
        value: status,
        onChange: onStatusChange,
        options: STATUS_OPTIONS
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_design_system_components_Select__WEBPACK_IMPORTED_MODULE_1__["default"], {
        id: "wpfm-feed-listing-channel-filter",
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Channel', 'rex-product-feed'),
        variant: "pill",
        value: channel,
        onChange: onChannelChange,
        options: resolvedChannelOptions
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)(_design_system_components_Select__WEBPACK_IMPORTED_MODULE_1__["default"], {
        id: "wpfm-feed-listing-date-filter",
        label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Date', 'rex-product-feed'),
        variant: "pill",
        value: dateRange,
        onChange: onDateRangeChange,
        options: DATE_OPTIONS
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("button", {
        type: "button",
        className: "wpfm-feed-listing__bulk-trash",
        disabled: 0 === selectedCount,
        onClick: onBulkTrash,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("svg", {
          width: "14",
          height: "14",
          viewBox: "0 0 16 16",
          fill: "none",
          "aria-hidden": "true",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
            d: "M2 4h12M6 4V2.5A1 1 0 0 1 7 1.5h2a1 1 0 0 1 1 1V4M6.5 7.5v4M9.5 7.5v4M3.5 4l.6 8.4A1.5 1.5 0 0 0 5.6 13.8h4.8a1.5 1.5 0 0 0 1.5-1.4l.6-8.4",
            stroke: "currentColor",
            strokeWidth: "1.3",
            strokeLinecap: "round",
            strokeLinejoin: "round"
          })
        }), (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Move to trash', 'rex-product-feed')]
      }), 'trash' === status && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("button", {
        type: "button",
        className: "wpfm-feed-listing__empty-trash",
        onClick: onEmptyTrash,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Empty Trash', 'rex-product-feed')
      })]
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("div", {
      className: "wpfm-feed-listing__search",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("input", {
        type: "search",
        value: search,
        placeholder: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Search feeds…', 'rex-product-feed'),
        onChange: e => onSearchChange(e.target.value)
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsxs)("svg", {
        width: "16",
        height: "18",
        viewBox: "0 0 18 20",
        fill: "none",
        "aria-hidden": "true",
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
          d: "M8.25 16.5C11.9779 16.5 15 13.1757 15 9.075C15 4.97428 11.9779 1.64999 8.25 1.64999C4.52208 1.64999 1.5 4.97428 1.5 9.075C1.5 13.1757 4.52208 16.5 8.25 16.5Z",
          stroke: "currentColor",
          strokeWidth: "1.5",
          strokeLinecap: "round",
          strokeLinejoin: "round"
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_2__.jsx)("path", {
          d: "M14.1974 17.0691C14.5949 18.3891 15.5024 18.5211 16.1999 17.3661C16.8374 16.3101 16.4174 15.4438 15.2624 15.4438C14.4074 15.4356 13.9274 16.1698 14.1974 17.0691Z",
          stroke: "currentColor",
          strokeWidth: "1.5",
          strokeLinecap: "round",
          strokeLinejoin: "round"
        })]
      })]
    })]
  });
}


/***/ },

/***/ "./src/feed-listing/components/ImportFeedModal.jsx"
/*!*********************************************************!*\
  !*** ./src/feed-listing/components/ImportFeedModal.jsx ***!
  \*********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ ImportFeedModal)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _design_system_components_Modal__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../design-system/components/Modal */ "./src/design-system/components/Modal.jsx");
/* harmony import */ var _compat_feedListingSync__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../compat/feedListingSync */ "./src/feed-listing/compat/feedListingSync.js");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__);





/**
 * Drag-and-drop XML import modal — thin client wrapper around Pro's
 * existing `rex_feed_save_import_xml_feed` admin-ajax handler (Task 15,
 * Phase 3). See `feedListingSync.js`'s `importXmlFeed()` for the wire
 * format and its "always wp_send_json_success(), check the message" quirk.
 *
 * @param {Object}   props
 * @param {boolean}  props.open
 * @param {Function} props.onClose
 * @param {string}   props.nonce      `importXmlNonce` from the feed-listing payload.
 * @param {Function} props.onImported Called after a real (not "Invalid file!") success.
 */

function ImportFeedModal({
  open,
  onClose,
  nonce,
  onImported
}) {
  const [file, setFile] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const [dragOver, setDragOver] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [uploading, setUploading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(false);
  const [error, setError] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useState)(null);
  const inputRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.useRef)(null);
  function resetAndClose() {
    setFile(null);
    setDragOver(false);
    setUploading(false);
    setError(null);
    onClose();
  }
  function acceptFile(candidate) {
    if (!candidate) {
      return;
    }
    const isXml = candidate.type === 'text/xml' || candidate.type === 'application/xml' || candidate.name.toLowerCase().endsWith('.xml');
    if (!isXml) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Please select an XML file.', 'rex-product-feed'));
      return;
    }
    setError(null);
    setFile(candidate);
  }
  function handleSubmit() {
    if (!file) {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Please select a file to import.', 'rex-product-feed'));
      return;
    }
    setUploading(true);
    setError(null);
    (0,_compat_feedListingSync__WEBPACK_IMPORTED_MODULE_3__.importXmlFeed)(file, nonce).then(response => {
      // Pro's handler calls wp_send_json_success() even for an
      // unrecognized file, with `message: 'Invalid file!'`
      // instead of using wp_send_json_error() — both cases must
      // be checked.
      if (response?.success && response?.data?.message !== 'Invalid file!') {
        resetAndClose();
        onImported();
        return;
      }
      setError(response?.data?.message || (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to import feed. Please check the file and try again.', 'rex-product-feed'));
    }).catch(() => {
      setError((0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Failed to import feed. Please check the file and try again.', 'rex-product-feed'));
    }).finally(() => setUploading(false));
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)(_design_system_components_Modal__WEBPACK_IMPORTED_MODULE_2__["default"], {
    open: open,
    onClose: uploading ? undefined : resetAndClose,
    title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import Feed', 'rex-product-feed'),
    className: "wpfm-import-feed-modal",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
      role: "button",
      tabIndex: 0,
      className: `wpfm-import-feed-modal__dropzone ${dragOver ? 'wpfm-import-feed-modal__dropzone--over' : ''}`,
      onDragOver: e => {
        e.preventDefault();
        setDragOver(true);
      },
      onDragLeave: () => setDragOver(false),
      onDrop: e => {
        e.preventDefault();
        setDragOver(false);
        acceptFile(e.dataTransfer.files?.[0]);
      },
      onClick: () => inputRef.current?.click(),
      onKeyDown: e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          inputRef.current?.click();
        }
      },
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("input", {
        ref: inputRef,
        type: "file",
        accept: ".xml,text/xml,application/xml",
        className: "wpfm-import-feed-modal__file-input",
        onChange: e => acceptFile(e.target.files?.[0])
      }), file ? /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("p", {
        children: file.name
      }) : /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("p", {
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Drag and drop an XML feed export here, or click to browse.', 'rex-product-feed')
      })]
    }), error && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("p", {
      className: "wpfm-import-feed-modal__error",
      children: error
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsxs)("div", {
      className: "wpfm-import-feed-modal__actions",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("button", {
        type: "button",
        onClick: resetAndClose,
        disabled: uploading,
        children: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Cancel', 'rex-product-feed')
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_4__.jsx)("button", {
        type: "button",
        className: "wpfm-import-feed-modal__submit",
        onClick: handleSubmit,
        disabled: uploading || !file,
        children: uploading ? (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Importing…', 'rex-product-feed') : (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)('Import', 'rex-product-feed')
      })]
    })]
  });
}

/***/ },

/***/ "./src/feed-listing/utils.js"
/*!***********************************!*\
  !*** ./src/feed-listing/utils.js ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dateRangeForLastDays: () => (/* binding */ dateRangeForLastDays),
/* harmony export */   formatRelativeTime: () => (/* binding */ formatRelativeTime)
/* harmony export */ });
/**
 * Relative-time formatting for the "Last Updated" column. Falls back to a
 * plain locale date string in the rare case `Intl.RelativeTimeFormat` isn't
 * available (very old browsers only — every currently-supported browser has
 * it).
 *
 * @param {string} isoString
 * @return {string}
 */
function formatRelativeTime(isoString) {
  if (!isoString) {
    return '—';
  }
  const date = new Date(isoString);
  if (isNaN(date.getTime())) {
    return '—';
  }
  const diffSeconds = Math.round((date.getTime() - Date.now()) / 1000);
  const divisions = [{
    amount: 60,
    unit: 'second'
  }, {
    amount: 60,
    unit: 'minute'
  }, {
    amount: 24,
    unit: 'hour'
  }, {
    amount: 7,
    unit: 'day'
  }, {
    amount: 4.34524,
    unit: 'week'
  }, {
    amount: 12,
    unit: 'month'
  }, {
    amount: Number.POSITIVE_INFINITY,
    unit: 'year'
  }];
  if ('undefined' === typeof Intl || !Intl.RelativeTimeFormat) {
    return date.toLocaleString();
  }
  const rtf = new Intl.RelativeTimeFormat(undefined, {
    numeric: 'auto'
  });
  let duration = diffSeconds;
  for (const division of divisions) {
    if (Math.abs(duration) < division.amount) {
      return rtf.format(Math.round(duration), division.unit);
    }
    duration /= division.amount;
  }
  return date.toLocaleString();
}

/**
 * Y-m-d for `days` ago, and for today — used by the listing's "Date" quick
 * filter (a simple pill Select, not the shared DateRangePicker: that
 * component defaults its own Select to a "Last 30 days" preset with no
 * "no filter" state, which doesn't match this page's "All dates" default —
 * see openspec/changes/feed-listing-ui/tasks.md 6.2).
 *
 * @param {number} days
 * @return {{after: string, before: string}}
 */
function dateRangeForLastDays(days) {
  const before = new Date();
  const after = new Date();
  after.setDate(after.getDate() - (days - 1));
  const toYmd = d => d.toISOString().slice(0, 10);
  return {
    after: toYmd(after),
    before: toYmd(before)
  };
}

/***/ },

/***/ "./src/feed-listing/styles/feed-listing.scss"
/*!***************************************************!*\
  !*** ./src/feed-listing/styles/feed-listing.scss ***!
  \***************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "react/jsx-runtime"
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
(module) {

module.exports = window["ReactJSXRuntime"];

/***/ },

/***/ "@wordpress/api-fetch"
/*!**********************************!*\
  !*** external ["wp","apiFetch"] ***!
  \**********************************/
(module) {

module.exports = window["wp"]["apiFetch"];

/***/ },

/***/ "@wordpress/element"
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
(module) {

module.exports = window["wp"]["element"];

/***/ },

/***/ "@wordpress/i18n"
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
(module) {

module.exports = window["wp"]["i18n"];

/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	const __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		const cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		const module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			const e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = (module) => {
/******/ 		const getter = module && module.__esModule ?
/******/ 			() => (module['default']) :
/******/ 			() => (module);
/******/ 		__webpack_require__.d(getter, { a: getter });
/******/ 		return getter;
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	// define getter/value functions for harmony exports
/******/ 	__webpack_require__.d = (exports, definition) => {
/******/ 		for(var key in definition) {
/******/ 			if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 				Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 			}
/******/ 		}
/******/ 	};
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	__webpack_require__.o = (obj, prop) => (Object.hasOwn(obj, prop));
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = (exports) => {
/******/ 		Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/ 	
/************************************************************************/
let __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!************************************!*\
  !*** ./src/feed-listing/index.jsx ***!
  \************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/api-fetch */ "@wordpress/api-fetch");
/* harmony import */ var _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _FeedListing__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./FeedListing */ "./src/feed-listing/FeedListing.jsx");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__);




const config = window.wpfmFeedListing ?? {};
if (config.nonce) {
  _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default().use(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default().createNonceMiddleware(config.nonce));
}
if (config.restUrl) {
  // Bare REST root — see FeedEditorPage.php's `restUrl` doc comment for
  // the double-prefix bug this avoids (every apiFetch({ path }) call here
  // already includes the full `/wpfm/v1/...` route).
  _wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default().use(_wordpress_api_fetch__WEBPACK_IMPORTED_MODULE_0___default().createRootURLMiddleware(config.restUrl));
}
const root = document.getElementById('wpfm-feed-listing-root');
if (root) {
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.createRoot)(root).render(/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_3__.jsx)(_FeedListing__WEBPACK_IMPORTED_MODULE_2__["default"], {}));
}
})();

/******/ })()
;
//# sourceMappingURL=index.js.map