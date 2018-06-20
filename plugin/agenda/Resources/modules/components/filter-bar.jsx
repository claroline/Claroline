import React from 'react'
import {PropTypes as T} from 'prop-types'
import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'

import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes'

function atrans(key) {
  return trans(key, {}, 'agenda')
}

const FilterBar = props =>
  <div className="col-md-3">
    <div className="panel">
      <div className="panel-body">
        <button className="btn btn-default btn-xs" role="button" onClick={props.openImportForm}>
          {trans('import', {}, 'platform')}
        </button>
        <a href={url(['apiv2_download_agenda', {workspace: props.workspace.id}])} className="btn btn-default btn-xs" role="button">
          {trans('export', {}, 'platform')}
        </a>
      </div>

      {props.workspaces &&
        <div className="panel panel-default">
          <div data-toggle="collapse" data-target="#panel-tasks" className="panel-heading">{atrans('filter_ws')}</div>
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
        <div data-toggle="collapse" data-target="#panel-tasks" className="panel-heading">{atrans('filter_tasks')}</div>
        <div id="panel-tasks" className="panel-body list-group in">
          <Checkboxes
            id="types"
            choices={{
              'task': 'tache',
              'event': 'évènement'
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
  </div>


FilterBar.propTypes = {
  openImportForm: T.func.isRequired,
  onChangeFiltersType: T.func.isRequired,
  onChangeFiltersWorkspace: T.func.isRequired,
  onExport: T.func.isRequired,
  workspace: T.object.isRequired,
  workspaces: T.object,
  filters: T.object.isRequired,
  reload: T.object.isRequired
}

export {
  FilterBar
}
