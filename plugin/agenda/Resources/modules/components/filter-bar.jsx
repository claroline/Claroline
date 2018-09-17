import React from 'react'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'

import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes'

const FilterBar = props =>
  <div className="col-md-3">
    {props.workspaces &&
      <div className="panel panel-default">
        <div data-toggle="collapse" data-target="#panel-tasks" className="panel-heading">{trans('filter_ws', {}, 'agenda')}</div>
        <div id="panel-tasks" className="panel-body list-group in">
          <Checkboxes
            id="workspaces"
            choices={props.workspaces}
            value={props.filters.workspaces}
            inline={false}
            onChange={(filters) => {
              props.onChangeFiltersWorkspace(filters, props.filters)
            }}
          />
        </div>
      </div>
    }

    <div className="panel panel-default">
      <div data-toggle="collapse" data-target="#panel-tasks" className="panel-heading">{trans('filter_tasks', {}, 'agenda')}</div>
      <div id="panel-tasks" className="panel-body list-group in">
        <Checkboxes
          id="types"
          choices={{
            task: trans('task', {}, 'agenda'),
            event: trans('event', {}, 'agenda')
          }}
          value={props.filters.types}
          inline={false}
          onChange={(filters) => {
            props.onChangeFiltersType(filters, props.filters)
          }}
        />
      </div>
    </div>
  </div>


FilterBar.propTypes = {
  onChangeFiltersType: T.func.isRequired,
  onChangeFiltersWorkspace: T.func.isRequired,
  workspace: T.object.isRequired,
  workspaces: T.object,
  filters: T.object.isRequired,
  reload: T.object.isRequired
}

export {
  FilterBar
}
