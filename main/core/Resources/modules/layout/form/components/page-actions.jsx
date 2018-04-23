import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {PageAction} from '#/main/core/layout/page'

const OpenAction = props =>
  <PageAction
    {...props}
  />

OpenAction.propTypes    = merge({}, PageAction.propTypes)
OpenAction.defaultProps = merge({}, PageAction.defaultProps, {
  icon: 'fa fa-pencil',
  label: trans('edit'),
  disabled: false,
  primary: true
})

const SaveAction = props =>
  <PageAction
    {...props}
    primary={true}
  />

SaveAction.propTypes    = merge({}, PageAction.propTypes)
SaveAction.defaultProps = merge({}, PageAction.defaultProps, {
  icon: 'fa fa-floppy-o',
  label: trans('save'),
  disabled: false
})

const CancelAction = props =>
  <PageAction
    {...props}
  />

CancelAction.propTypes    = merge({}, PageAction.propTypes)
CancelAction.defaultProps = merge({}, PageAction.defaultProps, {
  icon: 'fa fa-times',
  label: trans('cancel'),
  disabled: false
})

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
  open: T.object,
  save: T.object.isRequired,
  cancel: T.object
}

FormPageActions.defaultProps = {
  opened: false
}

export {
  FormPageActions
}