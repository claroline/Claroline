import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl'
import {percent} from '#/main/app/intl/number'
import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentSections, ContentSection} from '#/main/app/content/components/sections'
import {ProgressBar} from '#/main/app/content/components/progress-bar'
import {toKey} from '#/main/core/scaffolding/text'

import {getType} from '#/main/app/data/types'
import {formatField} from '#/main/app/content/form/parameters/utils'

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

const FormInputCount = (props) =>
  <TooltipOverlay
    id={props.id}
    tip={'percentage' === props.mode ? transChoice('stats_answers_count', props.count, {count: props.count}) : percent(props.count, props.total)+'%'}
    position="left"
  >
    {'percentage' === props.mode ?
      <div className="ms-auto d-inline-flex flex-direction-row gap-2 align-items-center">
        <small className={classes('fw-bold', `text-${props.variant}`)}>{percent(props.count, props.total)}%</small>
        <ProgressBar className="flex-shrink-0" style={{width: '80px'}} type={props.variant} value={percent(props.count, props.total)} />
      </div> :
      <small className={classes('ms-auto fw-bold text-nowrap', `text-${props.variant}`)}>{transChoice('stats_answers_count', props.count, {count: props.count})}</small>
    }
  </TooltipOverlay>

FormInputCount.propTypes = {
  id: T.string.isRequired,
  count: T.number,
  total: T.number,
  variant: T.oneOf(['primary', 'secondary']),
  mode: T.oneOf(['percentage', 'count'])
}

FormInputCount.defaultProps = {
  variant: 'primary'
}

const FormInputStats = (props) =>
  <Await
    for={getType(props.field.type)}
    then={definition => (
      <ContentSection
        {...omit(props, 'field', 'values', 'total', 'definition', 'mode')}
        title={
          <div className="d-flex flex-direction-row gap-3 align-items-center justify-content-between flex-wrap flex-md-nowrap me-3">
            <div className="flex-fill">
              {props.field.label}
              {props.field.help &&
                <small>{props.field.help}</small>
              }
            </div>

            <FormInputCount
              id={toKey(props.field.label)}
              count={props.values.reduce((acc, currentValue) => acc + currentValue.count, 0)}
              total={props.total}
              mode={props.mode}
            />
          </div>
        }
        icon={get(definition, 'meta.icon') &&
          <TooltipOverlay tip={get(definition, 'meta.label')} id={props.field.id} position="bottom">
            <span
              className={classes('icon-with-text-right', get(definition, 'meta.icon'))}
              aria-hidden={true}
            />
          </TooltipOverlay>
        }
        fill={true}
      >
        <table className="table table-striped table-borderless table-hover mb-0">
          <tbody>
            {isEmpty(props.values) &&
              <tr>
                <td>
                  {trans('empty_value')}
                </td>
                <td>{props.total}</td>
                <td>{percent(0, props.total)} %</td>
              </tr>
            }

            {props.values.map(value => (
              <tr key={value.value}>
                <StatsValue definition={definition} field={props.field} value={value.value} />
                <td align="right">
                  <FormInputCount
                    id={toKey(props.field.label)}
                    count={value.count}
                    total={props.total}
                    mode={props.mode}
                  />
                </td>
              </tr>
            ))}

            <tr>
              <td className="text-secondary fw-bold">
                {trans('stats_no_answer')}
              </td>
              <td align="right">
                <FormInputCount
                  id={toKey(props.field.label)}
                  count={props.total - props.values.reduce((acc, currentValue) => acc + currentValue.count, 0)}
                  total={props.total}
                  mode={props.mode}
                  variant="secondary"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </ContentSection>
    )}
  />

FormInputStats.propTypes = {
  field: T.object,
  definition: T.object,
  values: T.array,
  total: T.number,
  mode: T.oneOf(['percentage', 'count'])
}

FormInputStats.defaultProps = {
  values: []
}

const FormStats = (props) => {
  if (isEmpty(props.stats)) {
    return (
      <ContentLoader
        size="lg"
        description={trans('stats_loading')}
      />
    )
  }

  const allInputs = props.stats.fields.map(stats => stats.field.id)
  const [openedInputs, setOpenedInputs] = useState(allInputs)
  const [displayMode, setDisplayMode] = useState('percentage')

  return (
    <>
      <div className="d-flex flex-direction-row align-items-center mb-2">
        <span>{trans('stats_mode')}</span>
        <Button
          className="btn btn-text-primary fw-bold"
          type={CALLBACK_BUTTON}
          label={trans('percentage' === displayMode ? 'stats_mode_percentage' : 'stats_mode_count')}
          callback={() => setDisplayMode('percentage' === displayMode ? 'count' : 'percentage')}
        />
        <Button
          className="btn btn-text-primary ms-auto"
          type={CALLBACK_BUTTON}
          label={trans(!isEmpty(openedInputs) ? 'hide_all' : 'show_all')}
          callback={() => setOpenedInputs(!isEmpty(openedInputs) ? [] : allInputs)}
        />
      </div>

      <ContentSections
        className={props.className}
        level={3}
        accordion={false}
        opened={openedInputs}
        onSelect={setOpenedInputs}
      >
        {props.stats.fields.map(stats =>
          <FormInputStats
            key={stats.field.id}
            id={stats.field.id}
            field={stats.field}
            values={stats.values}
            total={props.stats.total}
            mode={displayMode}
          />
        )}
      </ContentSections>
    </>
  )
}

FormStats.propTypes = {
  className: T.string,
  flush: T.bool,
  stats: T.shape({
    total: T.number,
    fields: T.arrayOf(T.shape({
      field: T.object.isRequired,
      values: T.arrayOf(T.shape({
        value: T.any,
        count: T.number
      }))
    }))
  })
}

export {
  FormStats
}
