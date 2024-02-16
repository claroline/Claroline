import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import classes from 'classnames'

import {Popover} from '#/main/app/overlays/popover/components/popover'
import {trans} from '#/main/app/intl/translation'
import {Toolbar} from '#/main/app/action'
import {FormGroup} from '#/main/app/content/form/components/group'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {HtmlInput} from '#/main/app/data/types/html/components/input'
import {ColorInput} from '#/main/theme/data/types/color/components/input'
import {FeedbackEditorButton} from '#/plugin/exo/buttons/feedback/components/button'

export class AreaPopover extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFeedback: false
    }
  }

  render() {
    return (
      <Popover
        {...omit(this.props,
          'className',
          'color',
          'hasScore',
          'hasExpectedAnswers',
          'score',
          'feedback',
          'onChangeScore',
          'onChangeFeedback',
          'onPickColor',
          'onClose',
          'onDelete'
        )}
        id="area-popover"
        className={classes('area-popover', this.props.className)}
      >
        <Popover.Header className="d-flex align-items-center">
          {trans('graphic_area_edit', {}, 'quiz')}

          <Toolbar
            id="area-popover-actions"
            className="popover-actions ms-auto"
            tooltip="bottom"
            size="sm"
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                className: 'btn btn-text-danger',
                icon: 'fa fa-fw fa-trash',
                label: trans('delete', {}, 'actions'),
                callback: this.props.onDelete,
                displayed: !!this.props.onDelete
              }, {
                name: 'close',
                className: 'btn btn-text-secondary',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-times',
                label: trans('close', {}, 'actions'),
                callback: this.props.onClose
              }
            ]}
          />
        </Popover.Header>
        <Popover.Body>
          <div className={classes('form-group', 'base-controls', {'form-last': !this.state.showFeedback})}>
            <ColorInput
              id="area-color"
              className="color"
              hideInput={true}
              value={this.props.color}
              onChange={this.props.onPickColor}
            />

            <div className="right-controls">
              {this.props.hasExpectedAnswers && !this.props.hasScore &&
                <input
                  id="expected-answer"
                  type="checkbox"
                  className="form-check-input"
                  checked={0 < this.props.score}
                  onChange={(e) => this.props.onChangeScore(e.target.checked ? 1 : 0)}
                />
              }

              {this.props.hasExpectedAnswers && this.props.hasScore &&
                <input
                  type="number"
                  id="area-score"
                  className="form-control score"
                  value={this.props.score}
                  onChange={e => this.props.onChangeScore(parseFloat(e.target.value))}
                />
              }

              <FeedbackEditorButton
                id={this.props.id}
                label={trans('graphic_feedback_info', {}, 'quiz')}
                feedback={this.props.feedback}
                toggle={() => this.setState({showFeedback: !this.state.showFeedback})}
              />
            </div>
          </div>

          {this.state.showFeedback &&
            <FormGroup
              id="area-feedback"
              label={trans('feedback', {}, 'quiz')}
              hideLabel={true}
              className="mt-3 form-last"
            >
              <HtmlInput
                id="area-feedback"
                value={this.props.feedback}
                onChange={this.props.onChangeFeedback}
              />
            </FormGroup>
          }
        </Popover.Body>
      </Popover>
    )
  }
}

AreaPopover.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  color: T.string.isRequired,
  hasScore: T.bool.isRequired,
  hasExpectedAnswers: T.bool.isRequired,
  score: T.number.isRequired,
  feedback: T.string.isRequired,
  onChangeScore: T.func.isRequired,
  onChangeFeedback: T.func.isRequired,
  onPickColor: T.func.isRequired,
  onClose: T.func.isRequired,
  onDelete: T.func.isRequired
}
