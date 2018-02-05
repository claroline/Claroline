import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {t} from '#/main/core/translation'
import {Sections, Section} from '#/main/core/layout/components/sections.jsx'
import {getTypeOrDefault} from '#/main/core/data'
import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/core/data/details/prop-types'
import {createDetailsDefinition} from '#/main/core/data/details/utils'

// todo add `calculated` value like Forms and Lists
// todo there are big c/c from Form component but I don't know if we can do better

const DataDetailsField = props => {
  const typeDef = getTypeOrDefault(props.type)

  return (
    <div className="form-group">
      {!props.hideLabel &&
        <label className="control-label" htmlFor={props.name}>{props.label}</label>
      }

      <div id={props.name}>
        {!props.data &&
        <span className="data-details-empty">{t('empty_value')}</span>
        }
        {props.data &&
        (typeDef.components.details ?
            React.createElement(typeDef.components.details, merge({}, props.options, {
              id: props.name,
              label: props.label,
              hideLabel: props.hideLabel,
              data: props.data
            }))
            :
            typeDef.render ? typeDef.render(props.data, props.options || {}) : props.data
        )
        }
      </div>
    </div>
  )
}

DataDetailsField.propTypes = {
  data: T.any,
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  displayed: T.bool,
  options: T.object
}

const DataDetails = props => {
  const sections = createDetailsDefinition(props.sections)

  const primarySection = 1 === sections.length ? sections[0] : sections.find(section => section.primary)
  const otherSections = sections.filter(section => section !== primarySection)
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className)}>
      {primarySection &&
        <div className="panel panel-default">
          <div className="panel-body">
            {primarySection.fields.map(field =>
              <DataDetailsField
                {...field}
                key={field.name}
                data={get(props.data, field.name)}
              />
            )}
          </div>
        </div>
      }

      {0 !== otherSections.length &&
        <Sections
          level={props.level}
          defaultOpened={openedSection ? openedSection.id : undefined}
        >
          {otherSections.map(section =>
            <Section
              key={section.id}
              id={section.id}
              icon={section.icon}
              title={section.title}
            >
              {section.fields.map(field =>
                <DataDetailsField
                  {...field}
                  key={field.name}
                  data={get(props.data, field.name)}
                />
              )}
            </Section>
          )}
        </Sections>
      }
    </div>
  )
}

DataDetails.propTypes = {
  className: T.string,
  level: T.number,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  children: T.node
}

DataDetails.defaultProps = {
  level: 2,
  data: {}
}

export {
  DataDetails
}
