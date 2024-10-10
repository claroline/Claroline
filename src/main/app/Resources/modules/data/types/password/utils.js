import {match} from '#/main/app/data/types/validators'

function passwordStrength( password ) {
  let N = 0
  let L = password.length

  if (!match(password, {regex: /[a-z]/}) )
    N += 26
  if (!match(password, {regex: /[A-Z]/}) )
    N += 26
  if (!match(password, {regex: /[0-9]/}) )
    N += 10
  if (!match(password, {regex: /[^a-zA-Z0-9]/}) )
    N += 8

  const complexity = Math.pow(N, L)

  if (complexity < Math.pow(2, 64) )
    return 0
  else if (complexity < Math.pow(2, 84))
    return 1
  else if (complexity < Math.pow(2, 100))
    return 2
  else
    return 3
}

export {
  passwordStrength
}
