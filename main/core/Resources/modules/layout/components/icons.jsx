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
 * Copy icon.
 *
 * @param props
 * @constructor
 */
const CopyIcon = props =>
  <FontIcon {...props} name="copy" />

/**
 * Delete icon.
 *
 * @param props
 * @constructor
 */
const DeleteIcon = props =>
  <FontIcon {...props} name="trash-o" />

/**
 * Drag icon.
 *
 * @param props
 * @constructor
 */
const DragIcon = props =>
  <FontIcon {...props} name="arrows" />

/**
 * Edit icon.
 *
 * @param props
 * @constructor
 */
const EditIcon = props =>
  <FontIcon {...props} name="pencil" />

/**
 * Save icon.
 *
 * @param props
 * @constructor
 */
const SaveIcon = props =>
  <FontIcon {...props} name="save" />

/**
 * Published icon.
 *
 * @param props
 * @constructor
 */
const PublishedIcon = props =>
  <FontIcon {...props} name="eye" />

/**
 * Unpublished icon.
 *
 * @param props
 * @constructor
 */
const UnpublishedIcon = props =>
  <FontIcon {...props} name="eye-slash" />

export {
  FontIcon,
  CopyIcon,
  DeleteIcon,
  DragIcon,
  EditIcon,
  SaveIcon,
  PublishedIcon,
  UnpublishedIcon
}
