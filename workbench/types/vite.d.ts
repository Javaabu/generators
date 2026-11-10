// stencil's styles import CSS as a string through Vite
declare module "*.css?raw" {
  const css: string
  export default css
}
