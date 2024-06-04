import React, {Component} from 'react'
import omit from 'lodash/omit'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {TinymceEditor} from '#/main/app/input/tinymce/components/editor'

class HtmlInput extends Component {
  constructor(props) {
    super(props)

    this.state = {
      minimal: props.minimal,
      fullscreen: false
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
      <div id={`${this.props.id}-container`} className={classes('editor-control text-editor', this.props.className, {
        minimal: this.state.minimal && !this.state.fullscreen,
        fullscreen: this.state.fullscreen
      })}>
        {!this.state.fullscreen &&
          <Toolbar
            id={`${this.props.id}-toolbar`}
            name="editor-toolbar"
            className="btn-toolbar gap-1"
            buttonName="btn btn-body"
            tooltip="bottom"
            size="sm"
            actions={[
              {
                name: 'fullscreen',
                type: CALLBACK_BUTTON,
                label: trans('fullscreen_on'),
                icon: 'fa fa-fw fa-expand',
                callback: () => this.toggleEditor(this.state.minimal, !this.state.fullscreen)
              }, {
                name: 'toggle-editor',
                type: CALLBACK_BUTTON,
                icon: classes('fa fa-fw', {
                  'fa-plus': this.state.minimal,
                  'fa-minus': !this.state.minimal
                }),
                label: trans(this.state.minimal ? 'show_editor_toolbar' : 'hide_editor_toolbar'),
                callback: () => this.toggleEditor(!this.state.minimal, this.state.fullscreen)
              }
            ]}
          />
        }

        {this.state.fullscreen &&
          <Button
            className="fullscreen-close"
            type={CALLBACK_BUTTON}
            label={trans('fullscreen_off')}
            icon="fa fa-fw fa-times"
            callback={() => this.toggleEditor(this.state.minimal, !this.state.fullscreen)}
            tooltip="bottom"
          />
        }

        <TinymceEditor
          {...omit(this.props, 'onChangeMode')}
          id={this.props.id}
          mode={classes({
            inline: !this.state.fullscreen && this.state.minimal,
            classic: !this.state.fullscreen && !this.state.minimal,
            full: this.state.fullscreen
          })}
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
