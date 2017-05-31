import React, { Component } from 'react'
import {PropTypes as T} from 'prop-types'
import { DropdownButton, MenuItem } from 'react-bootstrap'
import {Icon as ItemIcon} from './../../items/components/icon.jsx'
import {getDefinition} from './../../items/item-types'

export default class ObjectSelector extends Component {
  renderExerciseMenu(exercise) {
    return (
      <MenuItem
        key={exercise.id}
        className="exercise-menu-item"
        onSelect={() => this.props.handleSelect('exercise', exercise.id) }
        active={'exercise' === this.props.current.type && this.props.current.id === exercise.id}
      >
        {exercise.title}
      </MenuItem>
    )
  }

  renderStepMenu(index, step) {
    return (
      <MenuItem
        key={step.id}
        className="step-menu-item"
        onSelect={() => this.props.handleSelect('step', step.id) }
        active={'step' === this.props.current.type && this.props.current.id === step.id}
      >
        <strong>Step #{index + 1}</strong>
        {step.title ? ' : ' + step.title : null}
      </MenuItem>
    )
  }

  renderQuestionMenu(question) {
    return (
      <MenuItem
        key={question.id}
        className="question-menu-item"
        onSelect={() => this.props.handleSelect('question', question.id) }
        active={'question' === this.props.current.type && this.props.current.id === question.id}
      >
        <ItemIcon name={getDefinition(question.type).name} />
        {question.title || question.content}
      </MenuItem>
    )
  }

  render() {
    const dropdownItems = []

    // Add exercise link
    dropdownItems.push(
      this.renderExerciseMenu(this.props.exercise)
    )

    for (let i = 0; i < this.props.exercise.steps.length; i++) {
      let step = this.props.exercise.steps[i]

      // Add header for the step
      dropdownItems.push(
        this.renderStepMenu(i, step)
      )

      // Add questions
      for (let j = 0; j < step.items.length; j++) {
        dropdownItems.push(
          this.renderQuestionMenu(step.items[j])
        )
      }
    }

    return (
      <div className="current-selector row">
        <div className="col-md-12">
          <DropdownButton
            id={'dropdown-select-current'}
            title={`${this.props.exercise.title}`}
            bsStyle={'default'}
            className="btn-block"
          >
            {dropdownItems}
          </DropdownButton>
        </div>
      </div>
    )
  }
}

ObjectSelector.propTypes = {
  exercise: T.object.isRequired,
  current: T.shape({
    id: T.string,
    type: T.string
  }),
  handleSelect: T.func.isRequired
}
