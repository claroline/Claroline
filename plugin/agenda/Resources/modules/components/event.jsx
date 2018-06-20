import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import moment from 'moment'

const EventPropLi = props =>
  <li className="list-group-item"> {trans(props.label, {}, 'agenda')} <span className="value"> {props.value} </span></li>

const Description = props =>
  <div className="well">
    {props.description}
  </div>

const Task = props =>
  <div>
    <ul className="list-group list-group-values">
      <EventPropLi label='limit_date' value={moment(props.end).format(trans('date_range.js_format'))}/>
      <EventPropLi label='form.author' value={props.owner.username}/>
      {props.workspace &&
        <EventPropLi label={trans('workspace')} value={props.workspace.code}/>
      }
    </ul>
    {props.description &&
      <Description description={props.description}/>
    }

    {props.isTaskDone ?
      <span>{trans('done')}</span>:
      <span>{trans('not_done')}</span>
    }
  </div>

const Event = props => {
  return(
    <div className="panel-body">
      <ul className="list-group list-group-values">
        <EventPropLi label='form.start' value={moment(props.start).format(trans('date_range.js_format'))}/>
        <EventPropLi label='form.end' value={moment(props.end).format(trans('date_range.js_format'))}/>
        <EventPropLi label='form.author' value={props.owner.username}/>
        {props.workspace &&
          <EventPropLi label={trans('workspace')} value={props.workspace.code}/>
        }
      </ul>

      {props.description &&
        <Description description={props.description}/>
      }

      {props.editable &&
        <div>
          <button className="btn btn-primary" onClick={props.onForm}>{trans('edit')}</button>
          {'\u00a0'}
          <button className="btn btn-danger" onClick={props.onDelete}>{trans('delete')}</button>
        </div>
      }
    </div>
  )}


Event.propTypes = {
  start: T.object.isRequired,
  owner: T.object.isRequired,
  end: T.object,
  onForm: T.func.isRequired,
  onDelete: T.func.isRequired,
  is_guest: T.bool.isRequired,
  editable: T.bool.isRequired,
  description: T.string,
  workspace: T.object
}

Task.propTypes = Event.propTypes

export {
  Task,
  Event
}
