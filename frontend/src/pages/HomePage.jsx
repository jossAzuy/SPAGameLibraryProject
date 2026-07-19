import { Link } from 'react-router-dom'

export default function HomePage() {
  return (
    <section className="mx-auto flex min-h-[calc(100vh-137px)] max-w-6xl items-center px-6 py-20">
      <div className="max-w-3xl">
        <span className="rounded-full border border-violet-500/30 bg-violet-500/10 px-4 py-2 text-sm text-violet-300">
          Catálogo inteligente de videojuegos
        </span>

        <h1 className="mt-6 text-5xl font-bold tracking-tight sm:text-6xl">
          Encuentra tu próximo videojuego
        </h1>

        <p className="mt-6 max-w-2xl text-lg text-slate-400">
          Explora el catálogo o describe el tipo de experiencia que buscas
          mediante la búsqueda semántica.
        </p>

        <div className="mt-10 flex flex-wrap gap-4">
          <Link
            to="/games"
            className="rounded-lg bg-violet-600 px-5 py-3 font-semibold transition hover:bg-violet-500"
          >
            Explorar juegos
          </Link>

          <Link
            to="/semantic-search"
            className="rounded-lg border border-slate-700 px-5 py-3 font-semibold transition hover:border-slate-500 hover:bg-slate-900"
          >
            Probar búsqueda semántica
          </Link>
        </div>
      </div>
    </section>
  )
}