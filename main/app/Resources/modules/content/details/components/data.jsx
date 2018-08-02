import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {toKey} from '#/main/core/scaffolding/text/utils'
import {Heading} from '#/main/core/layout/components/heading'
import {Sections, Section} from '#/main/core/layout/components/sections'

import {DataDetailsSection as DataDetailsSectionTypes} from '#/main/app/content/details/prop-types'
import {createDetailsDefinition} from '#/main/app/content/details/utils'
import {DetailsProp} from '#/main/app/content/details/components/prop'

// todo there are big c/c from Form component but I don't know if we can do better

const DetailsData = props => {
  const hLevel = props.level + (props.title ? 1 : 0)
  let hDisplay
  if (props.displayLevel) {
    hDisplay = props.displayLevel + (props.title ? 1 : 0)
  }

  const sections = createDetailsDefinition(props.sections)

  const primarySections = 1 === sections.length ? [sections[0]] : sections.filter(section => section.primary)
  const otherSections = 1 !== sections.length ? sections.filter(section => !section.primary) : []
  const openedSection = otherSections.find(section => section.defaultOpened)

  return (
    <div className={classes('data-details', props.className)}>
      {props.title &&
        <Heading level={props.level} displayLevel={props.displayLevel}>
          {props.title}
        </Heading>
      }

      {primarySections.map(primarySection =>
        <div key={toKey(primarySection.title)} className="panel panel-default primary-section">
          <div className="panel-body">
            <Heading level={hLevel} displayed={false}>
              {primarySection.title}
            </Heading>

            {primarySection.fields.map(field =>
              <DetailsProp
                {...field}
                key={field.name}
                data={props.data}
              />
            )}
          </div>
        </div>
      )}

      {0 !== otherSections.length &&
        <Sections
          level={hLevel}
          displayLevel={hDisplay}
          defaultOpened={openedSection ? openedSection.id : undefined}
        >
          {otherSections.map(section =>
            <Section
              key={toKey(section.title)}
              icon={section.icon}
              title={section.title}
            >
              {section.fields.map(field =>
                <DetailsProp
                  {...field}
                  key={field.name}
                  data={props.data}
                />
              )}
            </Section>
          )}
        </Sections>
      }

      {props.children}
    </div>
  )
}

DetailsData.propTypes = {
  className: T.string,
  level: T.number,
  displayLevel: T.number,
  title: T.string,
  data: T.object,
  sections: T.arrayOf(T.shape(
    DataDetailsSectionTypes.propTypes
  )).isRequired,
  children: T.node
}

DetailsData.defaultProps = {
  level: 2,
  data: {}
}

export {
  DetailsData
}
