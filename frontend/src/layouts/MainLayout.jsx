import { NavLink, Outlet } from 'react-router-dom'

const linkClass = ({ isActive }) =>
  [
    'rounded-lg px-3 py-2 text-sm font-medium transition',
    isActive
      ? 'bg-violet-600 text-white'
      : 'text-slate-300 hover:bg-slate-800 hover:text-white',
  ].join(' ')

export default function MainLayout() {
  return (
    <div className="min-h-screen bg-slate-950 text-slate-100">
      <header className="border-b border-slate-800 bg-slate-950/90">
        <nav className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
          <NavLink to="/" className="text-xl font-bold text-white">
            Game Library
          </NavLink>

          <div className="flex items-center gap-2">
            <NavLink to="/" className={linkClass}>
              Inicio
            </NavLink>

            <NavLink to="/games" className={linkClass}>
              Juegos
            </NavLink>

            <NavLink to="/semantic-search" className={linkClass}>
              Búsqueda semántica
            </NavLink>
          </div>
        </nav>
      </header>

      <main>
        <Outlet />
      </main>

      <footer className="border-t border-slate-800 px-6 py-6 text-center text-sm text-slate-500">
        Game Library · Laravel, React y búsqueda semántica
      </footer>
    </div>
  )
}