import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {PageAction} from '#/main/core/layout/page'

const OpenAction = props =>
  <PageAction
    id="form-open"
    title={props.label}
    icon={props.icon}
    disabled={props.disabled}
    action={props.action}
    primary={true}
  />

OpenAction.propTypes = {
  icon: T.string,
  label: T.string,
  disabled: T.bool,
  action: T.oneOfType([T.string, T.func]).isRequired
}

OpenAction.defaultProps = {
  icon: 'fa fa-pencil',
  label: trans('edit'),
  disabled: false
}

const SaveAction = props =>
  <PageAction
    id="form-save"
    title={props.label}
    icon={props.icon}
    action={props.action}
    disabled={props.disabled}
    primary={true}
  />

SaveAction.propTypes = {
  icon: T.string,
  label: T.string,
  disabled: T.bool,
  action: T.oneOfType([T.string, T.func]).isRequired
}

SaveAction.defaultProps = {
  icon: 'fa fa-floppy-o',
  label: trans('save'),
  disabled: false
}

const CancelAction = props =>
  <PageAction
    id="form-cancel"
    title={props.label}
    icon={props.icon}
    action={props.action}
    disabled={props.disabled}
  />

CancelAction.propTypes = {
  icon: T.string,
  label: T.string,
  disabled: T.bool,
  action: T.oneOfType([T.string, T.func]).isRequired
}

CancelAction.defaultProps = {
  icon: 'fa fa-times',
  label: trans('cancel'),
  disabled: false
}

const OpenedPageActions = props =>
  <span>
    <SaveAction
      key="form-save"
      {...props.save}
    />

    {props.cancel &&
      <CancelAction
        key="form-cancel"
        {...props.cancel}
      />
    }
  </span>

OpenedPageActions.propTypes = {
  save: T.object.isRequired,
  cancel: T.object
}

const ClosedPageActions = props =>
  <OpenAction
    {...props.open}
  />

ClosedPageActions.propTypes = {
  open: T.object.isRequired
}

const FormPageActions = props => props.opened ?
  <OpenedPageActions {...props} />
  :
  <ClosedPageActions {...props} />

FormPageActions.propTypes = {
  opened: T.bool,
  open: T.shape({
    label: T.string,
    icon: T.string,
    action: T.oneOfType([T.string, T.func]),
    disabled: T.bool
  }),

  save: T.shape({
    label: T.string,
    icon: T.string,
    action: T.func,
    disabled: T.bool
  }).isRequired,

  cancel: T.shape({
    label: T.string,
    icon: T.string,
    action: T.func,
    disabled: T.bool
  })
}

FormPageActions.defaultProps = {
  opened: false
}

export {
  FormPageActions
}