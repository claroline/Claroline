import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/plugin/exo/resources/quiz/editor/modals/item-position/store/selectors'

const PositionModal = props => {
  const stepChoices = props.steps
    .reduce((stepChoices, current) => Object.assign(stepChoices, {
      [current.id]: current.title
    }), {})

  const selectedParent = props.steps.find(step => props.positionData.parent ? step.id === props.positionData.parent: props.step.id)

  let i = 0

  const qChoices = (selectedParent.items || [])
    //.filter(item => item.id !== props.item.id)
    .reduce((qChoices, current) => Object.assign(qChoices, {
      [current.id]: current.title || trans('question') + ' ' + ++i
    }), {})

  // generate select actions
  const selectAction = props.selectAction(props.positionData)

  //props.update('parent', props.step.id)

  return (
    <Modal
      {...omit(props, 'step', 'steps', 'positionData', 'selectEnabled', 'selectAction', 'reset', 'update')}
      subtitle={props.step.title}
      onEntering={() => {
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

        props.reset(currentPosition)
      }}
    >
      <FormData
        name={selectors.STORE_NAME}
        sections={[
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
  items: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  item: T.arrayOf(T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired
  })),
  positionData: T.shape({
    order: T.oneOf(['first', 'before', 'after', 'last']),
    item: T.string,
    parent: T.string
  }),
  selectEnabled: T.bool,
  selectAction: T.func.isRequired, // action generator
  reset: T.func.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired,
  form: T.shape({

  })
}

PositionModal.defaultProps = {
  steps: [],
  questions: [],
  selectEnabled: false
}

export {
  PositionModal
}
