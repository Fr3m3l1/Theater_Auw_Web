import React from 'react';
export function Dialog({open=false,title,children,actions,onClose}){
  if(!open) return null;
  return <div onClick={onClose} style={{position:'fixed',inset:0,zIndex:100,background:'rgba(21,17,18,.62)',display:'flex',alignItems:'center',justifyContent:'center',padding:24}}>
    <div role="dialog" aria-modal onClick={e=>e.stopPropagation()}
      style={{background:'var(--surface-card)',borderRadius:'var(--radius-lg)',boxShadow:'var(--shadow-raised)',maxWidth:480,width:'100%',padding:'var(--sp-8)',fontFamily:'var(--font-body)'}}>
      <div style={{display:'flex',alignItems:'flex-start',justifyContent:'space-between',gap:16,marginBottom:12}}>
        <h2 style={{fontSize:'var(--text-h2)'}}>{title}</h2>
        <button aria-label="Schliessen" onClick={onClose} style={{background:'none',border:'none',cursor:'pointer',padding:4,color:'var(--gray-500)',lineHeight:0}}>
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
        </button>
      </div>
      <div style={{fontSize:15,color:'var(--text-body)'}}>{children}</div>
      {actions&&<div style={{display:'flex',justifyContent:'flex-end',gap:10,marginTop:'var(--sp-6)'}}>{actions}</div>}
    </div>
  </div>;
}
