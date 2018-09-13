import React from 'react'
import {PropTypes as T} from 'prop-types'

import {CallbackButton} from '#/main/app/buttons/callback/components/button'

import {trans} from '#/main/core/translation'
import {DateGroup} from '#/main/core/layout/form/components/group/date-group'

const Search = props =>
  <div className="panel panel-default">
    <div className="panel-body">
      <div className="col-md-6 col-xs-12">
        <DateGroup
          id="tracking-start-date"
          className="form-last"
          calendarIcon="fa fa fa-fw fa-calendar-check-o"
          label={trans('filter_from')}
          value={props.startDate}
          onChange={(date) => props.onChange('startDate', date)}
        />
      </div>
      <div className="col-md-6 col-xs-12">
        <DateGroup
          id="tracking-end-date"
          className="form-last"
          calendarIcon="fa fa fa-fw fa-calendar-check-o"
          label={trans('date_range_end')}
          value={props.endDate}
          minDate={props.startDate}
          onChange={(date) => props.onChange('endDate', date)}
        />
      </div>
      <div className="col-md-6 col-xs-12">
        <CallbackButton
          className="btn btn-primary traking-filter-button"
          callback={() => props.onSearch(props.startDate, props.endDate)}
        >
          {trans('filter')}
        </CallbackButton>
      </div>
    </div>
  </div>

Search.propTypes = {
  startDate: T.string,
  endDate: T.string,
  onChange: T.func.isRequired,
  onSearch: T.func.isRequired
}

export {
  Search
}
