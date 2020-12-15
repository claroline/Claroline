import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'

import {QUIZ_TYPES} from '#/plugin/exo/resources/quiz/types'

const CurrentType = props =>
  <Fragment>
    {props.icon &&
      <span className={props.icon} />
    }

    <div>
      <h1>{props.label}</h1>

      {props.description &&
        <p className="hidden-xs">{props.description}</p>
      }
    </div>
  </Fragment>

CurrentType.propTypes = {
  icon: T.string,
  label: T.string.isRequired,
  description: T.string
}

class QuizType extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false
    }

    this.setOpened = this.setOpened.bind(this)
  }

  setOpened(opened) {
    this.setState({opened: opened})
  }

  render() {
    const current = QUIZ_TYPES[this.props.type]

    return (
      <div className="quiz-type-control">
        <Button
          id="quiz-type"
          className="quiz-type form-control"
          type={MENU_BUTTON}
          label={current ?
            <CurrentType
              key="label"
              icon={current.meta.icon}
              label={current.meta.label}
              description={current.meta.description}
            /> :
            <CurrentType
              key="label"
              label={trans('select_quiz_type', {}, 'quiz')}
              description={trans('select_quiz_type_help', {}, 'quiz')}
            />
          }
          opened={this.state.opened}
          onToggle={this.setOpened}
          menu={
            <ul role="menu" className="dropdown-menu dropdown-menu-full">
              {Object.keys(QUIZ_TYPES).map(typeName => {
                const select = this.props.selectAction(typeName)

                return (
                  <li key={typeName} role="presentation">
                    <Button
                      {...select}
                      className="quiz-type"
                      active={typeName === this.props.type}
                      label={
                        <CurrentType
                          key="label"
                          icon={QUIZ_TYPES[typeName].meta.icon}
                          label={QUIZ_TYPES[typeName].meta.label}
                          description={QUIZ_TYPES[typeName].meta.description}
                        />
                      }
                      onClick={() => this.setOpened(false)}
                    />
                  </li>
                )
              })}
            </ul>
          }
        />
      </div>
    )
  }
}

QuizType.propTypes = {
  type: T.string,
  selectAction: T.func.isRequired
}

export {
  QuizType
}
