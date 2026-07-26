import React from 'react';
function Box({checked,round}){
  return <span aria-hidden style={{width:18,height:18,flex:'0 0 auto',display:'inline-flex',alignItems:'center',justifyContent:'center',
    borderRadius:round?'50%':4,border:`1.5px solid ${checked?'var(--red-600)':'var(--border-strong)'}`,
    background:checked&&!round?'var(--red-600)':'var(--surface-card)',transition:'all var(--dur-fast)'}}>
    {checked&&(round
      ?<span style={{width:9,height:9,borderRadius:'50%',background:'var(--red-600)'}}></span>
      :<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="#fff" strokeWidth="3.5" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6 9 17l-5-5"/></svg>)}
  </span>;
}
export function Checkbox({label,checked=false,onChange,disabled=false}){
  return <label style={{display:'inline-flex',alignItems:'center',gap:10,cursor:disabled?'not-allowed':'pointer',opacity:disabled?.45:1,fontFamily:'var(--font-body)',fontSize:15,color:'var(--text-body)'}}>
    <input type="checkbox" checked={checked} disabled={disabled} onChange={e=>onChange&&onChange(e.target.checked)} style={{position:'absolute',opacity:0,width:0}}/>
    <Box checked={checked}/>{label}
  </label>;
}
export function Radio({label,checked=false,onChange,name,disabled=false}){
  return <label style={{display:'inline-flex',alignItems:'center',gap:10,cursor:disabled?'not-allowed':'pointer',opacity:disabled?.45:1,fontFamily:'var(--font-body)',fontSize:15,color:'var(--text-body)'}}>
    <input type="radio" name={name} checked={checked} disabled={disabled} onChange={()=>onChange&&onChange(true)} style={{position:'absolute',opacity:0,width:0}}/>
    <Box checked={checked} round/>{label}
  </label>;
}
