import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/path/resources/path/editor/modals/position/store/selectors'

const PositionModal = props => {
  const parentChoices = props.steps
    .filter(step => step.id !== props.step.id)
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  const stepChoices = props.steps
    // only display the sub steps of the selected `parent`
    .filter(step => step.id !== props.step.id && get(step, 'parent.id') === props.positionData.parent)
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  // generate select actions
  const selectAction = props.selectAction(props.positionData)

  return (
    <Modal
      {...omit(props, 'step', 'steps', 'positionData', 'selectEnabled', 'selectAction', 'reset', 'update')}
      subtitle={props.step.title}
      onEntering={() => {
        // get the current step (I don't have access to `parent` in props.step)
        const currentStep = props.steps.find(step => step.id === props.step.id)

        // convert current step position to display in form
        const currentPosition = {}

        // get parent
        if (currentStep.parent) {
          currentPosition.parent = currentStep.parent.id
        }

        // get position between current parent children
        const siblings = props.steps.filter(step => get(step, 'parent.id') === get(currentStep, 'parent.id'))
        const siblingIndex = siblings.findIndex(step => step.id === currentStep.id)
        if (1 === siblings.length || 0 === siblingIndex) {
          // first or only child
          currentPosition.order = 'first'
        } else if (siblings.length === siblingIndex + 1) {
          // last child
          currentPosition.order = 'last'
        } else {
          currentPosition.order = 'after'
          currentPosition.step = siblings[siblingIndex - 1].id
        }

        props.reset(currentPosition)
      }}
    >
      <FormData
        name={selectors.STORE_NAME}
        sections={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'parent',
                label: trans('parent'),
                type: 'choice',
                options: {
                  condensed: true,
                  choices: parentChoices
                },
                onChange: () => {
                  props.update('order', 'last')
                  props.update('step', null)
                }
              }, {
                name: 'order',
                label: trans('position'),
                type: 'choice',
                required: true,
                options: {
                  condensed: true,
                  noEmpty: true,
                  choices: isEmpty(stepChoices) ? {
                    first: trans('first')
                  } : {
                    first: trans('first'),
                    before: trans('before'),
                    after: trans('after'),
                    last: trans('last')
                  }
                },
                onChange: (order) => {
                  if (-1 !== ['first', 'last'].indexOf(order)) {
                    props.update('step', null)
                  } else if (!props.positionData.step) {
                    // auto select a step
                    const siblings = Object.keys(stepChoices)
                    if (!isEmpty(siblings)) {
                      let step = siblings[siblings.length - 1]
                      if ('before' === order) {
                        step = siblings[0]
                      }

                      props.update('step', step)
                    }
                  }
                },
                linked: [
                  {
                    name: 'step',
                    label: trans('step', {}, 'path'),
                    type: 'choice',
                    required: true,
                    hideLabel: true,
                    displayed: (position) => position.order && -1 === ['first', 'last'].indexOf(position.order),
                    options: {
                      condensed: true,
                      noEmpty: true,
                      choices: stepChoices
                    }
                  }
                ]
              }
            ]
          }
        ]}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={!props.selectEnabled}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

PositionModal.propTypes = {
  title: T.string,
  step: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }),
  steps: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  positionData: T.shape({
    parent: T.string,
    order: T.oneOf(['first', 'before', 'after', 'last']),
    step: T.string
  }),
  selectEnabled: T.bool,
  selectAction: T.func.isRequired, // action generator
  reset: T.func.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

PositionModal.defaultProps = {
  steps: [],
  selectEnabled: false
}

export {
  PositionModal
}
