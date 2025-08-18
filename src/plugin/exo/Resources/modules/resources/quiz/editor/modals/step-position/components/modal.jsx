import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/exo/resources/quiz/editor/modals/step-position/store/selectors'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const PositionModal = props => {
  const stepChoices = props.steps
    .filter(step => step.id !== props.step.id)
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  // convert current step position to display in form
  const currentPosition = {}

  // get position
  const siblingIndex = props.steps.findIndex(step => step.id === props.step.id)
  if (1 === props.steps.length || 0 === siblingIndex) {
    // first or only child
    currentPosition.order = 'first'
  } else if (props.steps.length === siblingIndex + 1) {
    // last child
    currentPosition.order = 'last'
  } else {
    currentPosition.order = 'after'
    currentPosition.step = props.steps[siblingIndex - 1].id
  }

  return (
    <FormModal
      {...omit(props, 'step', 'steps', 'positionData', 'update')}
      subtitle={props.step.title}
      name={selectors.STORE_NAME}
      data={currentPosition}
      isNew={false}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
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
                  label: trans('step', {}, 'quiz'),
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
    order: T.oneOf(['first', 'before', 'after', 'last']),
    step: T.string
  }),
  saveLabel: T.string.isRequired,
  onSave: T.func.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

PositionModal.defaultProps = {
  steps: []
}

export {
  PositionModal
}
