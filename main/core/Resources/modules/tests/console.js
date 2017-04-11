/* eslint no-console: "off" */

// spy on console.error and stores error messages
export function watchConsole() {
  const originalError = console.error
  console._errors = []
  console.error = msg => console._errors.push(msg)
  console._restore = () => {
    console.error = originalError
    delete console._errors
    delete console._restore
  }
}

// restore a previously watched console
export function restoreConsole() {
  if (typeof console._restore !== 'function') {
    throw new Error(
      'Cannot restore console: console has not been watched or has already been restored'
    )
  }

  console._restore()
}