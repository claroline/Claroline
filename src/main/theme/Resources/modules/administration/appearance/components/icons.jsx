import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'
import {ThemeUrlIcon} from '#/main/theme/components/icon'

const IconPreview = (props) =>
  <TooltipOverlay id={props.url} tip={props.mimeTypes.join(', ')} position="bottom">
    <div className="theme-icon-preview">
      <ThemeUrlIcon size="md" url={props.url} svg={props.svg}/>
    </div>
  </TooltipOverlay>

IconPreview.propTypes = {
  mimeTypes: T.arrayOf(T.string),
  url: T.string.isRequired,
  svg: T.bool.isRequired
}

const IconSet = (props) =>
  <>
    <h3 className="h4">{trans(props.name)}</h3>
    <div className="theme-icon-preview-container mb-3">
      {[]
        .concat(props.icons)
        .sort((a, b) => a.url < b.url ? 1 : -1)
        .map((icon) =>
          <IconPreview key={icon.url} {...icon} />
        )
      }
    </div>
  </>

IconSet.propTypes = {
  name: T.string.isRequired,
  icons: T.arrayOf(T.shape({
    mimeTypes: T.arrayOf(T.string),
    url: T.string.isRequired,
    svg: T.bool.isRequired
  }))
}

const AppearanceIcons = (props) => {
  if (!props.currentIconSet) {
    return null
  }

  return (
    <>
      <IconSet
        name="resources"
        icons={get(props.currentIconSet, 'icons.resources')}
      />

      <IconSet
        name="widgets"
        icons={get(props.currentIconSet, 'icons.widgets')}
      />

      <IconSet
        name="data"
        icons={get(props.currentIconSet, 'icons.data')}
      />
    </>
  )
}

AppearanceIcons.propTypes = {
  currentIconSet: T.shape({
    name: T.string,
    icons: T.shape({

    })
  })
}

export {
  AppearanceIcons
}
