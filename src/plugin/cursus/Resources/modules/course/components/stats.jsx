import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {Alert} from '#/main/app/alert/components/alert'
import {Await} from '#/main/app/components/await'
import {getType} from '#/main/app/data/types'
import {formatField} from '#/main/app/content/form/parameters/utils'
import {percent} from '#/main/app/intl/number'

const StatsValue = (props) => {
  const fieldDef = formatField(props.field, [], null, true)

  return (
    <td>
      {props.definition.render(props.value, fieldDef.options || {})}
    </td>
  )
}

StatsValue.propTypes = {
  field: T.object.isRequired,
  definition: T.object.isRequired,
  value: T.any
}

const CourseStats = (props) =>
  <Fragment>
    <Alert type="info">
      {trans('course_stats_help', {}, 'cursus')}
    </Alert>

    {props.stats &&
      <Fragment>
        <table className="table table-striped table-hover">
          <tbody>
            {props.stats.fields.map(stats => (
              <Fragment key={stats.field.id}>
                <tr>
                  <th scope="col" colSpan="3">
                    {stats.field.label}
                  </th>
                </tr>

                {isEmpty(stats.values) &&
                  <tr>
                    <td>
                      {trans('empty_value')}
                    </td>
                    <td>{props.stats.total}</td>
                    <td>{percent(0, props.stats.total)} %</td>
                  </tr>
                }

                {stats.values.map(value => (
                  <tr key={value.value}>
                    <Await
                      for={getType(stats.field.type)}
                      then={definition => (
                        <StatsValue definition={definition} field={stats.field} value={value.value} />
                      )}
                    />
                    <td>{value.count}</td>
                    <td>{percent(value.count, props.stats.total)} %</td>
                  </tr>
                ))}
              </Fragment>
            ))}
          </tbody>
          <tfoot>
            <tr>
              <th scope="row">
                {trans('count_registered_users', {}, 'cursus')}
              </th>
              <td colSpan={2}>{props.stats.total}</td>
            </tr>
          </tfoot>
        </table>
      </Fragment>
    }
  </Fragment>

CourseStats.propTypes = {
  course: T.object.isRequired,
  stats: T.shape({
    total: T.number,
    fields: T.arrayOf(T.shape({
      field: T.object.isRequired,
      values: T.array
    }))
  })
}

export {
  CourseStats
}
