import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'

import {selectors} from '#/plugin/exo/resources/quiz/editor/modals/item-position/store/selectors'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

const PositionModal = props => {
  const stepChoices = props.steps
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  const selectedParent = props.steps.find(step => props.positionData.parent ? step.id === props.positionData.parent: props.step.id)

  let i = 0

  const qChoices = (selectedParent.items || [])
    .reduce((qChoices, current) => Object.assign(qChoices, {
      [current.id]: current.title || trans('item', {number: i + 1}, 'quiz')
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
          title: trans('parent'),
          primary: true,
          fields: [
            {
              name: 'parent',
              label: trans('parent'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: stepChoices
              },
              onChange: () => {
                props.update('order', 'last')
              }
            },
            {
              name: 'order',
              label: trans('position'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: isEmpty(qChoices) ? {
                  first: trans('first')
                } : {
                  first: trans('first'),
                  before: trans('before'),
                  after: trans('after'),
                  last: trans('last')
                }
              },
              onChange: () => {
                if (!props.positionData.parent) {
                  props.update('parent', props.steps[0].id)
                }

                if (!props.positionData.item) {
                  props.update('item', Object.keys(qChoices)[0])
                }
              },
              linked: [
                {
                  name: 'item',
                  label: trans('question', {}, 'quiz'),
                  type: 'choice',
                  required: true,
                  hideLabel: true,
                  displayed: (position) => position.order && -1 === ['first', 'last'].indexOf(position.order),
                  options: {
                    condensed: true,
                    noEmpty: true,
                    choices: qChoices
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
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  }),
  positionData: T.shape({
    order: T.oneOf(['first', 'before', 'after', 'last']),
    item: T.string,
    parent: T.string
  }),
  saveLabel: T.string.isRequired,
  onSave: T.func.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

PositionModal.defaultProps = {
  steps: [],
  questions: []
}

export {
  PositionModal
}
