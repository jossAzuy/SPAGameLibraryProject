import { useState } from 'react'
import { Link } from 'react-router-dom'
import { semanticSearchGames } from '../services/gameService'

export default function SemanticSearchPage() {
  const [query, setQuery] = useState('')
  const [results, setResults] = useState([])
  const [meta, setMeta] = useState(null)
  const [isLoading, setIsLoading] = useState(false)
  const [error, setError] = useState(null)
  const [hasSearched, setHasSearched] = useState(false)

  async function handleSubmit(event) {
    event.preventDefault()

    const normalizedQuery = query.trim()

    if (!normalizedQuery) {
      setError('Escribe una descripción para realizar la búsqueda.')
      return
    }

    try {
      setIsLoading(true)
      setError(null)
      setHasSearched(true)

      const response = await semanticSearchGames(normalizedQuery, 6)

      setResults(response.data ?? [])
      setMeta(response.meta ?? null)
    } catch (requestError) {
      console.error(requestError)

      setResults([])
      setMeta(null)
      setError('No fue posible realizar la búsqueda semántica.')
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <section className="mx-auto min-h-[calc(100vh-137px)] max-w-6xl px-6 py-12">
      <div className="max-w-3xl">
        <h1 className="text-3xl font-bold">
          Búsqueda semántica
        </h1>

        <p className="mt-3 text-slate-400">
          Describe el tipo de juego que buscas. Puedes escribir frases como
          “un RPG de fantasía con exploración” o “un juego cooperativo de
          acción”.
        </p>
      </div>

      <form
        onSubmit={handleSubmit}
        className="mt-8 rounded-xl border border-slate-800 bg-slate-900 p-5"
      >
        <label
          htmlFor="semantic-query"
          className="block text-sm font-medium text-slate-200"
        >
          ¿Qué tipo de juego buscas?
        </label>

        <div className="mt-3 flex flex-col gap-3 sm:flex-row">
          <input
            id="semantic-query"
            type="text"
            value={query}
            onChange={(event) => setQuery(event.target.value)}
            placeholder="Ejemplo: un juego de aventura con exploración"
            className="min-w-0 flex-1 rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-slate-100 outline-none transition focus:border-sky-500"
          />

          <button
            type="submit"
            disabled={isLoading}
            className="rounded-lg bg-sky-600 px-6 py-3 font-semibold text-white transition hover:bg-sky-500 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {isLoading ? 'Buscando...' : 'Buscar'}
          </button>
        </div>
      </form>

      {error && (
        <div className="mt-8 rounded-lg border border-red-900 bg-red-950/40 p-4 text-red-300">
          {error}
        </div>
      )}

      {meta && !isLoading && !error && (
        <div className="mt-8 flex flex-wrap items-center gap-3 text-sm text-slate-400">
          <span>
            Consulta:
            <strong className="ml-1 text-slate-200">
              {meta.query}
            </strong>
          </span>

          <span>•</span>

          <span>
            {meta.count} resultado{meta.count !== 1 ? 's' : ''}
          </span>
        </div>
      )}

      {!isLoading &&
        !error &&
        hasSearched &&
        results.length === 0 && (
          <p className="mt-10 text-slate-400">
            No se encontraron juegos relacionados con la búsqueda.
          </p>
        )}

      {!isLoading && !error && results.length > 0 && (
        <div className="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {results.map((result) => {
            const game = result.game
            const similarityPercentage = Math.round(
              Number(result.similarity) * 100,
            )

            return (
              <Link
                key={game.id}
                to={`/games/${game.id}`}
                className="group block"
              >
                <article className="h-full overflow-hidden rounded-xl border border-slate-800 bg-slate-900 transition duration-200 group-hover:-translate-y-1 group-hover:border-sky-600">
                  {game.cover_url && (
                    <img
                      src={game.cover_url}
                      alt={`Portada de ${game.title}`}
                      className="aspect-video w-full object-cover"
                      loading="lazy"
                    />
                  )}

                  <div className="p-5">
                    <div className="flex items-start justify-between gap-3">
                      <h2 className="text-xl font-semibold transition-colors group-hover:text-sky-400">
                        {game.title}
                      </h2>

                      <span className="shrink-0 rounded-full bg-sky-950 px-3 py-1 text-xs font-semibold text-sky-300">
                        {similarityPercentage}% similar
                      </span>
                    </div>

                    {game.description && (
                      <p className="mt-3 line-clamp-3 text-sm text-slate-400">
                        {game.description}
                      </p>
                    )}

                    <div className="mt-4 flex flex-wrap gap-2">
                      {game.genres?.slice(0, 3).map((genre) => (
                        <span
                          key={genre}
                          className="rounded-full border border-slate-700 px-2.5 py-1 text-xs text-slate-300"
                        >
                          {genre}
                        </span>
                      ))}
                    </div>

                    <div className="mt-5 flex items-center justify-between text-sm text-slate-400">
                      <span>
                        {game.release_year ?? 'Año desconocido'}
                      </span>

                      {game.rating !== null &&
                        game.rating !== undefined && (
                          <span>
                            Rating: {game.rating}/10
                          </span>
                        )}
                    </div>
                  </div>
                </article>
              </Link>
            )
          })}
        </div>
      )}
    </section>
  )
}