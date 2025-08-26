import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isNumber from 'lodash/isNumber'
import get from 'lodash/get'

import {DataDisplay} from '#/main/app/data/components/display'

const DescriptionList = ({
  className,
  fields,
  inline = false,
  bordered = true,
  variant = null,
  loaded = true,
  data = null
}) => {
  const displayedFields = fields
    .filter(field => {
      if (undefined === field.displayed) {
        return true
      }

      return typeof field.displayed === 'function' ? field.displayed(data) : field.displayed
    })
    .sort((a, b) => {
      if (isNumber(a.order) && !isNumber(b.order)) {
        return -1
      } else if (!isNumber(a.order) && isNumber(b.order)) {
        return 1
      } else if (isNumber(a.order) && isNumber(b.order)) {
        return a.order - b.order
      }

      return 0
    })

  return (
    <dl className={classes(variant && `border-${variant} border-opacity-25`, {
      'border-top border-bottom': bordered
    }, className)}>
      {displayedFields.map((field, index) => {
        let value
        if (undefined !== field.calculated) {
          value = typeof field.calculated === 'function' ? field.calculated(data) : field.calculated
        } else {
          value = get(data, field.name)
        }

        let customInput
        if (field.component) {
          customInput = field.component
        } else if (field.render) {
          customInput = field.render(data)
        }

        return (
          <div key={field.name} className={classes('py-3', variant && `border-${variant} border-opacity-25`, {
            'border-bottom': bordered && index !== displayedFields.length - 1,
            'd-flex flex-row align-items-start gap-3': inline
          })} role="presentation">
            <dt className={classes(variant && `text-${variant}-emphasis`, {
              'w-25 mb-0': inline
            })}>
              <span className={classes({'placeholder rounded-1': !loaded})} role="presentation">
                {field.icon &&
                  <span className={classes('fa-fw me-3', field.icon)} aria-hidden={true} />
                }
                {field.label}
              </span>
            </dt>

            <dd className={classes('mb-0',variant && `text-${variant}-emphasis`,  {
              'w-75': inline
            })}>
              {!loaded ?
                <span className={classes(index % 2 === 0 ? 'w-75':'w-50', {'placeholder rounded-1': !loaded})}>&nbsp;</span> :
                <DataDisplay
                  key={field.name}
                  type={field.type}
                  options={field.options}
                  placeholder={field.placeholder}
                  value={value}
                >
                  {customInput}
                </DataDisplay>
              }
            </dd>
          </div>
        )
      })}
    </dl>
  )
}

DescriptionList.propTypes = {
  className: T.string,
  inline: T.bool,
  bordered: T.bool,
  variant: T.oneOf(['success', 'info', 'warning', 'danger', 'secondary']),
  loaded: T.bool,
  data: T.object,
  fields: T.arrayOf(T.shape({
    order: T.number,
    type: T.string.isRequired,
    displayed: T.oneOfType([T.bool, T.func]),
    help: T.oneOfType([
      T.string,
      T.arrayOf(T.string)
    ])
  })).isRequired
}

export {
  DescriptionList
}
