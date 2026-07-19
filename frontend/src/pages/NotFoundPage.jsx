import { Link } from 'react-router-dom'

export default function NotFoundPage() {
  return (
    <section className="flex min-h-[calc(100vh-137px)] items-center justify-center px-6">
      <div className="text-center">
        <p className="text-sm font-semibold text-violet-400">Error 404</p>
        <h1 className="mt-3 text-4xl font-bold">Página no encontrada</h1>

        <Link
          to="/"
          className="mt-8 inline-block rounded-lg bg-violet-600 px-5 py-3 font-semibold hover:bg-violet-500"
        >
          Volver al inicio
        </Link>
      </div>
    </section>
  )
}