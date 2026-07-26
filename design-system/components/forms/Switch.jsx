import React from 'react';
export function Switch({label,checked=false,onChange,disabled=false}){
  return <label style={{display:'inline-flex',alignItems:'center',gap:10,cursor:disabled?'not-allowed':'pointer',opacity:disabled?.45:1,fontFamily:'var(--font-body)',fontSize:15,color:'var(--text-body)'}}>
    <input type="checkbox" role="switch" checked={checked} disabled={disabled} onChange={e=>onChange&&onChange(e.target.checked)} style={{position:'absolute',opacity:0,width:0}}/>
    <span aria-hidden style={{width:38,height:22,borderRadius:11,padding:2,boxSizing:'border-box',background:checked?'var(--red-600)':'var(--gray-400)',transition:'background var(--dur-med) var(--ease-out)',display:'inline-block'}}>
      <span style={{display:'block',width:18,height:18,borderRadius:'50%',background:'#fff',transform:checked?'translateX(16px)':'none',transition:'transform var(--dur-med) var(--ease-out)'}}></span>
    </span>{label}
  </label>;
}
