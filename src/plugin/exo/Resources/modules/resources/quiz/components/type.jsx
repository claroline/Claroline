import React, {Component, forwardRef, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON} from '#/main/app/buttons'
import {Menu} from '#/main/app/overlays/menu'

import {QUIZ_TYPES} from '#/plugin/exo/resources/quiz/types'

const CurrentType = props =>
  <Fragment>
    {props.icon &&
      <span className={props.icon} />
    }

    <div className="text-wrap">
      <span className={`h${props.level}`}>{props.label}</span>

      {props.description &&
        <small className="d-block">{props.description}</small>
      }
    </div>
  </Fragment>

CurrentType.propTypes = {
  icon: T.string,
  level: T.number.isRequired,
  label: T.string.isRequired,
  description: T.string
}

const TypeDropdown = forwardRef((props, ref) =>
  <div
    {...omit(props, 'type', 'selectAction', 'closeMenu')}
    role="menu"
    className={classes('dropdown-menu-full', props.className)}
    ref={ref}
  >
    {Object.keys(QUIZ_TYPES).map(typeName => {
      const select = props.selectAction(typeName)

      return (
        <Button
          {...select}
          key={typeName}
          className="dropdown-item quiz-type"
          active={typeName === props.type}
          label={
            <CurrentType
              key="label"
              level={5}
              icon={QUIZ_TYPES[typeName].meta.icon}
              label={QUIZ_TYPES[typeName].meta.label}
              description={QUIZ_TYPES[typeName].meta.description}
            />
          }
          onClick={props.closeMenu}
        />
      )
    })}
  </div>
)

TypeDropdown.propTypes = {
  className: T.string,
  type: T.string,
  selectAction: T.func.isRequired,
  closeMenu: T.func.isRequired
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
          variant="btn"
          size="lg"
          className="quiz-type"
          type={MENU_BUTTON}
          label={current ?
            <CurrentType
              key="label"
              icon={current.meta.icon}
              label={current.meta.label}
              description={current.meta.description}
              level={4}
            /> :
            <CurrentType
              key="label"
              label={trans('select_quiz_type', {}, 'quiz')}
              description={trans('select_quiz_type_help', {}, 'quiz')}
              level={4}
            />
          }
          opened={this.state.opened}
          onToggle={this.setOpened}
          menu={
            <Menu
              as={TypeDropdown}
              type={this.props.type}
              selectAction={this.props.selectAction}
              closeMenu={() => this.setOpened(false)}
            />
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
