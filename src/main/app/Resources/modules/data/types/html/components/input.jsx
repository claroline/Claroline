import React, {Component} from 'react'
import omit from 'lodash/omit'
import classes from 'classnames'
import CloseButton from 'react-bootstrap/CloseButton'

import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {TinymceEditor} from '#/main/app/input/tinymce/components/editor'
import {getValidationClassName} from '#/main/app/content/form/validator'

class HtmlInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      minimal: props.minimal,
      fullscreen: false,
      focus: false
    }

    this.toggleEditor = this.toggleEditor.bind(this)
  }

  toggleEditor(minimal, fullscreen) {
    this.setState({
      minimal: minimal,
      fullscreen: fullscreen
    }, () => {
      if (this.props.onChangeMode) {
        this.props.onChangeMode({
          minimal: minimal,
          fullscreen: fullscreen
        })
      }
    })
  }

  render() {
    return (
      <div id={`${this.props.id}-container`} className={classes('editor-control text-editor', this.props.className, getValidationClassName(this.props.error), {
        minimal: this.state.minimal && !this.state.fullscreen,
        fullscreen: this.state.fullscreen,
        focus: this.state.focus
      })} role="presentation">
        {!this.state.fullscreen &&
          <Toolbar
            id={`${this.props.id}-toolbar`}
            name="editor-toolbar"
            className="btn-toolbar gap-1"
            buttonName="btn btn-body focus-ring"
            tooltip="bottom"
            size="sm"
            disabled={this.props.disabled}
            actions={[
              {
                name: 'toggle-editor',
                type: CALLBACK_BUTTON,
                icon: classes('fa fa-fw', {
                  'fa-plus': this.state.minimal,
                  'fa-minus': !this.state.minimal
                }),
                label: trans(this.state.minimal ? 'show_editor_toolbar' : 'hide_editor_toolbar'),
                callback: () => this.toggleEditor(!this.state.minimal, this.state.fullscreen)
              }, {
                name: 'fullscreen',
                type: CALLBACK_BUTTON,
                label: trans('fullscreen_on'),
                icon: 'fa fa-fw fa-expand',
                callback: () => this.toggleEditor(this.state.minimal, !this.state.fullscreen)
              }
            ]}
          />
        }

        {this.state.fullscreen &&
          <CloseButton
            className="position-absolute top-0 end-0 m-2 p-2 z-3"
            aria-label={trans('close', {}, 'actions')}
            onClick={() => this.toggleEditor(this.state.minimal, !this.state.fullscreen)}
          />
        }

        <TinymceEditor
          {...omit(this.props, 'onChangeMode')}
          id={this.props.id}
          className={getValidationClassName(this.props.error)}
          mode={classes({
            inline: !this.state.fullscreen && this.state.minimal,
            classic: !this.state.fullscreen && !this.state.minimal,
            full: this.state.fullscreen
          })}
          onFocusIn={() => this.setState({focus: true})}
          onFocusOut={() => this.setState({focus: false})}
        />
      </div>
    )
  }
}

implementPropTypes(HtmlInput, DataInputTypes, {
  // more precise value type
  value: T.string,
  // custom props
  minimal: T.bool,
  minRows: T.number,
  workspace: T.object,
  onChangeMode: T.func
}, {
  minRows: 4,
  minimal: true
})

export {
  HtmlInput
}
