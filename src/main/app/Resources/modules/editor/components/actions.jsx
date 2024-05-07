import React, {useContext} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'

import {EditorPage} from '#/main/app/editor/components/page'
import {EditorContext} from '#/main/app/editor/context'

const ActionCard = (props) =>
  <article className={classes('card mb-2', props.dangerous && 'border-danger')}>
    <div className="card-body d-flex flex-row gap-3 align-items-start" role="presentation">
      <div className="flex-fill" role="presentation">
        <b className="d-block mb-2">
          {props.title}
          {props.managerOnly &&
            <span className={classes(' ms-2 badge', props.dangerous ? 'text-danger-emphasis bg-danger-subtle' : 'text-secondary-emphasis bg-secondary-subtle')}>{trans('confidentiality_manager')}</span>
          }
        </b>
        <p className="card-text text-body-secondary fs-sm">{props.help}</p>
      </div>

      <Button
        className={classes('btn', props.dangerous ? 'btn-danger' : 'btn-body')}
        {...props.action}
      />

    </div>
  </article>

ActionCard.propTypes = {
  title: T.string.isRequired,
  help: T.string.isRequired,
  action: T.object.isRequired,
  managerOnly: T.bool,
  dangerous: T.bool
}

const EditorActions = (props) => {
  const editorDef = useContext(EditorContext)

  const actions = props.actions
    .filter(action => (undefined === action.displayed || action.displayed) && !action.dangerous && (!action.managerOnly || editorDef.canAdministrate))

  const dangerousActions = props.actions
    .filter(action => (undefined === action.displayed || action.displayed)  && action.dangerous && (!action.managerOnly || editorDef.canAdministrate))

  return (
    <EditorPage
      title={trans('Actions avancées')}
      help={trans('Ut enim ad minima veniam, quis nostrum exercitationem ullam corporis suscipit laboriosam, nisi ut aliquid ex ea commodi consequatur?')}
    >
      {actions.map(action =>
        <ActionCard {...action} />
      )}

      {!isEmpty(actions) && !isEmpty(dangerousActions) &&
        <hr className="mt-3 mb-4"/>
      }

      {dangerousActions.map((action) =>
        <ActionCard {...action} />
      )}
    </EditorPage>
  )
}

EditorActions.propTypes = {
  actions: T.arrayOf(T.shape({
    title: T.string.isRequired,
    help: T.string.isRequired,
    managerOnly: T.bool,
    displayed: T.bool,
    action: T.object.isRequired,
    dangerous: T.bool
  }))
}

EditorActions.defaultProps = {
  actions: []
}

export {
  EditorActions
}