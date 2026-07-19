import http from '../api/http'

export async function getGames() {
  const response = await http.get('/games')

  return response.data
}

export async function getGame(id) {
  const response = await http.get(`/games/${id}`)

  return response.data
}

export async function semanticSearchGames(query, limit = 6) {
  const response = await http.get('/games/semantic-search', {
    params: {
      q: query,
      limit,
    },
  })

  return response.data
}