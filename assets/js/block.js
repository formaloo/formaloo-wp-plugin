"use strict";

Object.defineProperty(exports, "__esModule", {
  value: true
});
exports.default = void 0;

var _classnames = _interopRequireDefault(require("classnames"));

function _interopRequireDefault(obj) {
  return obj && obj.__esModule ? obj : { default: obj };
}

/**
 * Block dependencies
 */

/**
 * Internal block libraries
 */
var __ = wp.i18n.__;
var registerBlockType = wp.blocks.registerBlockType;
var _wp$editor = wp.editor,
  RichText = _wp$editor.RichText,
  InspectorControls = _wp$editor.InspectorControls,
  BlockControls = _wp$editor.BlockControls;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  TextControl = _wp$components.TextControl,
  ToggleControl = _wp$components.ToggleControl,
  SelectControl = _wp$components.SelectControl,
  Dashicon = _wp$components.Dashicon,
  Toolbar = _wp$components.Toolbar,
  Button = _wp$components.Button,
  Tooltip = _wp$components.Tooltip;
/**
 * Register block
 */

var _default = registerBlockType("formaloo-gutenberg/url-to-show-form", {
  // Block Title
  title: __("Formaloo Block"),
  // Block Description
  description: __("Use this block to show forms from Formaloo."),
  // Block Category
  category: "embedded",
  // Block Icon
  icon: "twitter",
  // Block Keywords
  keywords: [__("Form"), __("Forms"), __("Contact")],
  attributes: {
    /*
    slug: {
    	type: 'string',
    	default: 'slug',
    },
    address: {
    	type: 'string',
    	default: 'address',
    },
    */
    url: {
      type: "string"
    },
    type: {
      type: "string",
      default: "link"
    },
    link_title: {
      type: "string",
      default: __("Show Form")
    },
    show_title: {
      type: "string",
      default: "yes"
    },
    show_descr: {
      type: "string",
      default: "yes"
    },
    show_logo: {
      type: "string",
      default: "yes"
    }
  },
  // Defining the edit interface
  edit: function edit(props) {
    var onChangeURL = function onChangeURL(value) {
      props.setAttributes({
        url: value
      });
    };

    var onChangeShowType = function onChangeShowType(value) {
      props.setAttributes({
        type: value
      });
    };

    var onChangeLinkTitle = function onChangeLinkTitle(value) {
      props.setAttributes({
        link_title: value
      });
    };

    var onChangeShowLogo = function onChangeShowLogo(value) {
      props.setAttributes({
        show_logo: value
      });
    };

    var onChangeShowTitle = function onChangeShowTitle(value) {
      props.setAttributes({
        show_title: value
      });
    };

    var onChangeShowDescription = function onChangeShowDescription(value) {
      props.setAttributes({
        show_descr: value
      });
    };

    return [
      !!props.isSelected &&
        React.createElement(
          InspectorControls,
          {
            key: "inspector"
          },
          React.createElement(
            PanelBody,
            {
              title: __("Form Settings")
            },
            React.createElement(SelectControl, {
              label: __("Show Type"),
              options: [
                {
                  label: __("Link"),
                  value: "link"
                },
                {
                  label: __("iFrame"),
                  value: "iframe"
                },
                {
                  label: __("Script"),
                  value: "script"
                }
              ],
              value: props.attributes.type,
              onChange: onChangeShowType
            }),
            React.createElement(TextControl, {
              label: __("Link Title"),
              value: props.attributes.link_title,
              onChange: onChangeLinkTitle
            }),
            React.createElement(ToggleControl, {
              label: __("Show Logo"),
              checked: props.attributes.show_logo,
              onChange: onChangeShowLogo
            }),
            React.createElement(ToggleControl, {
              label: __("Show Title"),
              checked: props.attributes.show_title,
              onChange: onChangeShowTitle
            }),
            React.createElement(ToggleControl, {
              label: __("Show Description"),
              checked: props.attributes.show_descr,
              onChange: onChangeShowDescription
            })
          )
        ),
      React.createElement(
        "div",
        {
          className: props.className
        },
        React.createElement(
          "div",
          null,
          React.createElement(
            "div",
            {
              className: "ctt-text"
            },
            React.createElement(RichText, {
              format: "string",
              formattingControls: [],
              placeholder: __("Enter the form URL"),
              onChange: onChangeURL,
              value: props.attributes.url
            })
          ),
          React.createElement(
            "p",
            null,
            React.createElement(
              "a",
              {
                className: "ctt-btn"
              },
              __("Show Form")
            )
          )
        )
      )
    ];
  },
  // Defining the front-end interface
  save: function save() {
    // Rendering in PHP
    return null;
  }
});

exports.default = _default;
