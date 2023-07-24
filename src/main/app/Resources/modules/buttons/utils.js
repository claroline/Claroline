import classes from 'classnames'

function buttonClasses(className, variant, size, disabled, active, primary, dangerous) {
  let computedClasses = classes(size && `btn-${size}`, {
    disabled: disabled,
    active: active
  })

  if (variant) {
    computedClasses = classes('btn', variant, computedClasses)

    let hasClassVariant = -1 !== [
      'primary',
      'secondary',
      'success',
      'danger',
      'warning',
      'info'
    ].findIndex(variantType => (className || '').includes(`${variant}-${variantType}`))

    if (!hasClassVariant) {
      if (['btn'].includes(variant) && !primary && !dangerous) {
        computedClasses = classes(computedClasses, {
          // for retro-compatibility, because default button has been replaced by secondary
          // but secondary is already used for user progression in claroline
          'btn-outline-primary': !primary && !dangerous,
          [`${variant}-primary`]: primary,
          [`${variant}-danger`]: dangerous
        })
      } else {
        computedClasses = classes(computedClasses, {
          [`${variant}-default`]: !primary && !dangerous,
          [`${variant}-primary`]: primary,
          [`${variant}-danger`]: dangerous
        })
      }
    }
  }

  return classes(className, computedClasses)
}

export {
  buttonClasses
}
