import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

/**
 * Base component for icons using font (currently use FontAwesome).
 *
 * @param props
 * @constructor
 */
const FontIcon = props =>
  <span
    className={classes(
      'fa',
      'fa-'+props.name,
      {'fa-fw': props.fixedWidth},
      props.className
    )}
    aria-hidden="true"
  />

FontIcon.propTypes = {
  name: T.string.isRequired,
  fixedWidth: T.bool,
  className: T.string
}

// declare semantic icons in order to avoid wrong icon use

/**
 * Delete icon.
 *
 * @param props
 * @constructor
 */
const DeleteIcon = props =>
  <FontIcon {...props} name="trash-o" />

DeleteIcon.propTypes = {
  fixedWidth: T.bool,
  className: T.string
}

/**
 * Edit icon.
 *
 * @param props
 * @constructor
 */
const EditIcon = props =>
  <FontIcon {...props} name="pencil" />

EditIcon.propTypes = {
  fixedWidth: T.bool,
  className: T.string
}

/**
 * Save icon.
 *
 * @param props
 * @constructor
 */
const SaveIcon = props =>
  <FontIcon {...props} name="save" />

SaveIcon.propTypes = {
  fixedWidth: T.bool,
  className: T.string
}

/**
 * Published icon.
 *
 * @param props
 * @constructor
 */
const PublishedIcon = props =>
  <FontIcon {...props} name="eye" />

PublishedIcon.propTypes = {
  fixedWidth: T.bool,
  className: T.string
}

/**
 * Unpublished icon.
 *
 * @param props
 * @constructor
 */
const UnpublishedIcon = props =>
  <FontIcon {...props} name="eye-slash" />

UnpublishedIcon.propTypes = {
  fixedWidth: T.bool,
  className: T.string
}

export {
  FontIcon,
  DeleteIcon,
  EditIcon,
  SaveIcon,
  PublishedIcon,
  UnpublishedIcon
}
