import React, {useEffect, useState} from 'react'
import { PropTypes as T } from 'prop-types'
import classes from 'classnames'
import tinycolor from 'tinycolor2'
import { Select } from '#/main/app/input/components/select'
import { trans } from '#/main/app/intl/translation'
import { CallbackButton } from '#/main/app/buttons/callback/components/button'

const ColorChartLibrary = (props) => {
  const [selectedColorChart, setSelectedColorChart] = useState('all')

  const handleColorChartSelect = (selectedOption) => {
    setSelectedColorChart(selectedOption)
  }

  useEffect(() => {
    setSelectedColorChart('all')
  }, [props.colorCharts])

  const selectedObject = tinycolor(props.selected)

  return (
    <>
      {props.colorCharts && props.colorCharts.length > 1 && (
        <Select
          id="color-chart-select"
          size="sm"
          noEmpty={true}
          value={selectedColorChart}
          onChange={handleColorChartSelect}
          choices={(props.colorCharts || []).reduce((choices, colorChart) => {
            return { ...choices, [colorChart.name]: colorChart.name }
          }, {
            'all': trans('all')
          })}
        />
      )}

      <div className="color-chart-library">
        {(props.colorCharts || []).map((colorChart) => (colorChart.colors || []).map(color => {
          if (colorChart.name === selectedColorChart || selectedColorChart === 'all') {
            const colorObject = tinycolor(color)

            return (
              <CallbackButton
                key={color}
                className="color-dot md"
                style={{
                  background: color
                }}
                callback={() => props.onChange(color)}
              >
                {(props.selected && selectedObject.toRgbString() === colorObject.toRgbString()) &&
                  <span
                    className={classes('fa fa-check', {
                      'text-light': colorObject.isDark(),
                      'text-dark': colorObject.isLight()
                    })}/>}
                <span className="sr-only">{color}</span>
              </CallbackButton>
            )
          }

          return null
        }))}
      </div>
    </>
  )
}

ColorChartLibrary.propTypes = {
  selected: T.string,
  onChange: T.func.isRequired,
  colorCharts: T.array
}

export {
  ColorChartLibrary
}
